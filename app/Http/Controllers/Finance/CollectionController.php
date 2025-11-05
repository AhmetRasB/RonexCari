<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $collections = Collection::with('customer')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('finance.collections.index', compact('collections'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        return view('finance.collections.create', compact('customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Collection store method called', [
            'request_all' => $request->all(),
            'request_method' => $request->method(),
            'request_url' => $request->fullUrl(),
            'user_id' => auth()->id(),
            'session_account_id' => session('current_account_id'),
        ]);
        
        try {
            Log::info('Starting validation');
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'collection_type' => 'required|in:nakit,banka,kredi_karti,havale,eft,cek',
                'transaction_date' => 'required|date',
                'amount' => 'required|numeric|min:0.01',
                'discount' => 'nullable|numeric|min:0',
                'currency' => 'required|in:TRY,USD,EUR',
                'description' => 'nullable|string|max:1000'
            ]);
            Log::info('Validation passed', ['validated' => $validated]);
            
            // İndirim tutarı - boş string veya null ise 0 yap
            $discount = $validated['discount'] ?? 0;
            if ($discount === '' || $discount === null) {
                $discount = 0;
            }
            $discount = (float) $discount;
            $validated['discount'] = $discount;
            // amount = ödenen tutar, discount = indirim, toplam borç = amount + discount
            
            Log::info('Discount processed', ['discount' => $discount, 'amount' => $validated['amount']]);
            
            // Account ID'yi ekle
            $accountId = session('current_account_id');
            if (!$accountId) {
                $account = \App\Models\Account::active()->first();
                $accountId = $account ? $account->id : 1; // Default to first account
            }
            $validated['account_id'] = $accountId;
            Log::info('Account ID set', ['account_id' => $accountId]);

            DB::beginTransaction();
            Log::info('Database transaction started');

            $collection = Collection::create($validated);
            Log::info('Collection created', ['collection_id' => $collection->id, 'collection_data' => $collection->toArray()]);

            // Müşterinin bakiyesini güncelle 
            $customer = Customer::find($validated['customer_id']);
            Log::info('Customer lookup', ['customer_id' => $validated['customer_id'], 'customer_found' => $customer ? 'yes' : 'no']);
            
            if ($customer) {
                $currencyField = 'balance_' . strtolower($validated['currency']);
                Log::info('Currency field check', ['currency_field' => $currencyField, 'field_exists' => in_array($currencyField, ['balance_try', 'balance_usd', 'balance_eur'])]);
                
                if (in_array($currencyField, ['balance_try', 'balance_usd', 'balance_eur'])) {
                    $currentBalance = $customer->$currencyField;
                    
                    Log::info('Collection balance update', [
                        'customer_id' => $customer->id,
                        'customer_name' => $customer->name,
                        'currency_field' => $currencyField,
                        'current_balance' => $currentBalance,
                        'payment_amount' => $validated['amount'],
                        'discount' => $discount
                    ]);
                    
                    // Tahsilat yapıldığında borç azalır (bakiye azalır)
                    // amount = ödenen tutar, discount = indirim
                    // Toplam borçtan düşülecek tutar = ödenen tutar + indirim
                    // Örnek: 90.000 borç, 10.000 indirim, 80.000 ödeme
                    // Bakiyeden: 80.000 (ödenen) + 10.000 (indirim) = 90.000 düşülmeli
                    $totalReduction = $validated['amount'] + $discount;
                    $newBalance = $currentBalance - $totalReduction;
                    $customer->$currencyField = $newBalance;
                    Log::info('Collection made, debt reduced', [
                        'old_balance' => $currentBalance,
                        'payment_amount' => $validated['amount'],
                        'discount' => $discount,
                        'total_reduction' => $totalReduction,
                        'new_balance' => $newBalance
                    ]);
                    
                    $customer->save();
                    Log::info('Customer balance updated successfully');
                }
            }

            DB::commit();

            Log::info('Collection created successfully', ['collection_id' => $collection->id]);

            return redirect()->route('finance.collections.index')
                ->with('success', 'Tahsilat başarıyla kaydedildi.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Collection creation failed', ['error' => $e->getMessage()]);
            
            return back()->withInput()
                ->with('error', 'Tahsilat kaydedilirken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Collection $collection)
    {
        $collection->load('customer');
        return view('finance.collections.show', compact('collection'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Collection $collection)
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        return view('finance.collections.edit', compact('collection', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Collection $collection)
    {
        try {
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'collection_type' => 'required|in:nakit,banka,kredi_karti,havale,eft',
                'transaction_date' => 'required|date',
                'amount' => 'required|numeric|min:0.01',
                'currency' => 'required|in:TRY,USD,EUR',
                'description' => 'nullable|string|max:1000'
            ]);

            DB::beginTransaction();

            // Eski tutarı geri ekle (tahsilatı iptal et)
            $oldCustomer = $collection->customer;
            if ($oldCustomer) {
                $oldCurrencyField = 'balance_' . strtolower($collection->currency);
                if (in_array($oldCurrencyField, ['balance_try', 'balance_usd', 'balance_eur'])) {
                    // Tahsilat iptal edilince tahsilat etkisini tersine çevir
                    // Tahsilat yapıldığında bakiye azalmıştı, şimdi geri ekle
                    $oldCustomer->$oldCurrencyField += $collection->amount;
                    $oldCustomer->save();
                }
            }

            $collection->update($validated);

            // Yeni tutarı çıkar
            $newCustomer = Customer::find($validated['customer_id']);
            if ($newCustomer) {
                $newCurrencyField = 'balance_' . strtolower($validated['currency']);
                if (in_array($newCurrencyField, ['balance_try', 'balance_usd', 'balance_eur'])) {
                    $currentBalance = $newCustomer->$newCurrencyField;
                    
                    // Tahsilat yapıldığında borç azalır (bakiye azalır)
                    $newCustomer->$newCurrencyField = $currentBalance - $validated['amount'];
                    
                    $newCustomer->save();
                }
            }

            DB::commit();

            Log::info('Collection updated successfully', ['collection_id' => $collection->id]);

            return redirect()->route('finance.collections.index')
                ->with('success', 'Tahsilat başarıyla güncellendi.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Collection update failed', ['error' => $e->getMessage()]);
            
            return back()->withInput()
                ->with('error', 'Tahsilat güncellenirken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Collection $collection)
    {
        try {
            DB::beginTransaction();

            // Müşterinin bakiyesini geri ekle (tahsilat silinince borç geri gelir)
            $customer = $collection->customer;
            if ($customer) {
                $currencyField = 'balance_' . strtolower($collection->currency);
                
                // Tahsilat yapıldığında: balance -= (amount + discount)
                // Tahsilat silinince: balance += (amount + discount)
                $discount = (float) ($collection->discount ?? 0);
                $totalAmount = $collection->amount + $discount;
                
                if (in_array($currencyField, ['balance_try', 'balance_usd', 'balance_eur'])) {
                    $currentBalance = $customer->$currencyField ?? 0;
                    $newBalance = $currentBalance + $totalAmount;
                    $customer->$currencyField = $newBalance;
                    
                    // Also update legacy balance field
                    $customer->balance = ($customer->balance ?? 0) + $totalAmount;
                    $customer->save();
                    
                    Log::info('Customer balance updated after collection deletion', [
                        'customer_id' => $customer->id,
                        'collection_id' => $collection->id,
                        'currency' => $collection->currency,
                        'amount' => $collection->amount,
                        'discount' => $discount,
                        'total_added' => $totalAmount,
                        'old_balance' => $currentBalance,
                        'new_balance' => $newBalance
                    ]);
                }
            }

            $collection->delete();

            DB::commit();

            Log::info('Collection deleted successfully', ['collection_id' => $collection->id]);

            return redirect()->route('finance.collections.index')
                ->with('success', 'Tahsilat başarıyla silindi.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Collection deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'collection_id' => $collection->id ?? 'unknown'
            ]);
            
            return back()->with('error', 'Tahsilat silinirken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Search customers for AJAX
     */
    public function searchCustomers(Request $request)
    {
        try {
            $query = $request->get('q');
            
            if (strlen($query) < 2) {
                return response()->json([]);
            }

            $customers = Customer::where('is_active', true)
                ->where(function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%")
                      ->orWhere('phone', 'like', "%{$query}%")
                      ->orWhere('tax_number', 'like', "%{$query}%");
                })
                ->limit(10)
                ->get(['id', 'name', 'email', 'phone']);

            return response()->json($customers);

        } catch (\Exception $e) {
            Log::error('Customer search failed', ['error' => $e->getMessage()]);
            return response()->json([], 500);
        }
    }

    /**
     * Get customer balances for AJAX
     */
    public function getCustomerBalances(Request $request)
    {
        try {
            $customerId = $request->get('customer_id');
            
            if (!$customerId) {
                return response()->json(['error' => 'Customer ID required'], 400);
            }

            $customer = Customer::find($customerId);
            
            if (!$customer) {
                return response()->json(['error' => 'Customer not found'], 404);
            }

            return response()->json([
                'balance_try' => (float)($customer->balance_try ?? 0),
                'balance_usd' => (float)($customer->balance_usd ?? 0),
                'balance_eur' => (float)($customer->balance_eur ?? 0),
            ]);

        } catch (\Exception $e) {
            Log::error('Get customer balances failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    /**
     * Print collection receipt
     */
    public function print(Collection $collection)
    {
        // Load customer relationship
        $collection->load('customer');
        
        // Convert amount to words (with currency-aware units)
        $amountInWords = $this->numberToWords($collection->amount, $collection->currency);

        // Language for print
        $lang = request()->get('lang', 'tr');
        $translations = $this->getCollectionPrintTranslations($lang);
        
        // Remaining customer balances in all currencies
        $remainingBalances = null;
        if ($collection->customer) {
            $remainingBalances = [
                'TRY' => (float) ($collection->customer->balance_try ?? 0),
                'USD' => (float) ($collection->customer->balance_usd ?? 0),
                'EUR' => (float) ($collection->customer->balance_eur ?? 0),
            ];
        }
        
        return view('finance.collections.print', compact('collection', 'amountInWords', 'remainingBalances', 'translations', 'lang'));
    }

    /**
     * Convert number to Turkish words
     */
    private function numberToWords($number, $currency = 'TRY')
    {
        $ones = [
            '', 'bir', 'iki', 'üç', 'dört', 'beş', 'altı', 'yedi', 'sekiz', 'dokuz',
            'on', 'on bir', 'on iki', 'on üç', 'on dört', 'on beş', 'on altı', 'on yedi', 'on sekiz', 'on dokuz'
        ];
        
        $tens = [
            '', '', 'yirmi', 'otuz', 'kırk', 'elli', 'altmış', 'yetmiş', 'seksen', 'doksan'
        ];
        
        $groups = [
            '', 'bin', 'milyon', 'milyar'
        ];

        if ($number == 0) {
            return 'sıfır';
        }

        $number = number_format($number, 2, '.', '');
        $parts = explode('.', $number);
        $integerPart = (int)$parts[0];
        $decimalPart = isset($parts[1]) ? (int)$parts[1] : 0;

        $result = $this->convertIntegerToWords($integerPart, $ones, $tens, $groups);
        
        // Currency-aware unit labels
        $currency = strtoupper($currency ?? 'TRY');
        $unitMain = 'lira';
        $unitSub = 'kuruş';
        if ($currency === 'USD') { $unitMain = 'dolar'; $unitSub = 'sent'; }
        elseif ($currency === 'EUR') { $unitMain = 'euro'; $unitSub = 'sent'; }
        
        if ($decimalPart > 0) {
            $result .= ' ' . $unitMain . ' ' . $this->convertIntegerToWords($decimalPart, $ones, $tens, $groups) . ' ' . $unitSub;
        } else {
            $result .= ' ' . $unitMain;
        }

        return ucfirst(trim($result));
    }

    private function getCollectionPrintTranslations(string $lang): array
    {
        $lang = strtolower($lang);
        $tr = [
            'receipt' => 'Tahsilat Makbuzu',
            'receipt_no' => 'Makbuz No',
            'date' => 'Tarih',
            'customer' => 'Müşteri',
            'company' => 'Şirket',
            'tax_no' => 'Vergi No',
            'address' => 'Adres',
            'collection_type' => 'Tahsilat Türü',
            'currency' => 'Para Birimi',
            'description' => 'Açıklama',
            'collected_amount' => 'Tahsil Edilen Tutar',
            'in_words' => 'Yazıyla',
            'remaining_all' => 'Kalan Bakiye (Tüm Para Birimleri)',
            'collector' => 'Tahsil Eden',
            'collected_from' => 'Tahsil Edilen',
        ];
        $en = [
            'receipt' => 'Collection Receipt',
            'receipt_no' => 'Receipt No',
            'date' => 'Date',
            'customer' => 'Customer',
            'company' => 'Company',
            'tax_no' => 'Tax No',
            'address' => 'Address',
            'collection_type' => 'Collection Type',
            'currency' => 'Currency',
            'description' => 'Description',
            'collected_amount' => 'Collected Amount',
            'in_words' => 'In Words',
            'remaining_all' => 'Remaining Balance (All Currencies)',
            'collector' => 'Collector',
            'collected_from' => 'Collected From',
        ];
        $ar = [
            'receipt' => 'إيصال التحصيل',
            'receipt_no' => 'رقم الإيصال',
            'date' => 'التاريخ',
            'customer' => 'العميل',
            'company' => 'الشركة',
            'tax_no' => 'الرقم الضريبي',
            'address' => 'العنوان',
            'collection_type' => 'نوع التحصيل',
            'currency' => 'العملة',
            'description' => 'الوصف',
            'collected_amount' => 'المبلغ المحصل',
            'in_words' => 'كتابة',
            'remaining_all' => 'الرصيد المتبقي (جميع العملات)',
            'collector' => 'القابض',
            'collected_from' => 'من المحصل منه',
        ];
        $ru = [
            'receipt' => 'Квитанция о получении',
            'receipt_no' => '№ квитанции',
            'date' => 'Дата',
            'customer' => 'Клиент',
            'company' => 'Компания',
            'tax_no' => 'ИНН',
            'address' => 'Адрес',
            'collection_type' => 'Тип оплаты',
            'currency' => 'Валюта',
            'description' => 'Описание',
            'collected_amount' => 'Полученная сумма',
            'in_words' => 'Сумма прописью',
            'remaining_all' => 'Остаток задолженности (все валюты)',
            'collector' => 'Получил',
            'collected_from' => 'От',
        ];
        return match($lang){
            'en' => $en,
            'ar' => $ar,
            'ru' => $ru,
            default => $tr,
        };
    }

    private function convertIntegerToWords($number, $ones, $tens, $groups)
    {
        if ($number == 0) return '';
        
        $result = '';
        $groupIndex = 0;
        
        while ($number > 0) {
            $group = $number % 1000;
            
            if ($group > 0) {
                $groupText = '';
                
                // Hundreds
                $hundreds = intval($group / 100);
                if ($hundreds > 0) {
                    if ($hundreds == 1) {
                        $groupText .= 'yüz ';
                    } else {
                        $groupText .= $ones[$hundreds] . ' yüz ';
                    }
                }
                
                // Tens and ones
                $remainder = $group % 100;
                if ($remainder < 20) {
                    if ($remainder > 0) {
                        $groupText .= $ones[$remainder] . ' ';
                    }
                } else {
                    $tensDigit = intval($remainder / 10);
                    $onesDigit = $remainder % 10;
                    
                    $groupText .= $tens[$tensDigit];
                    if ($onesDigit > 0) {
                        $groupText .= ' ' . $ones[$onesDigit];
                    }
                    $groupText .= ' ';
                }
                
                // Add group name (bin, milyon, etc.)
                if ($groupIndex > 0) {
                    if ($groupIndex == 1 && $group == 1) {
                        $groupText = 'bin ';
                    } else {
                        $groupText .= $groups[$groupIndex] . ' ';
                    }
                }
                
                $result = $groupText . $result;
            }
            
            $number = intval($number / 1000);
            $groupIndex++;
        }
        
        return trim($result);
    }

    public function bulkDelete(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $ids = json_decode($request->input('ids'), true);
            if (empty($ids) || !is_array($ids)) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Geçersiz seçim');
            }
            
            Log::info('Bulk delete collections started', [
                'ids' => $ids,
                'count' => count($ids)
            ]);
            
            // Get collections with their customers before deletion
            $collections = Collection::with('customer')->whereIn('id', $ids)->get();
            
            // Update customer balances before deleting
            foreach ($collections as $collection) {
                $customer = $collection->customer;
                if ($customer) {
                    $currencyField = 'balance_' . strtolower($collection->currency);
                    
                    // Tahsilat yapıldığında: balance -= (amount + discount)
                    // Tahsilat silinince: balance += (amount + discount)
                    $discount = (float) ($collection->discount ?? 0);
                    $totalAmount = $collection->amount + $discount;
                    
                    if (in_array($currencyField, ['balance_try', 'balance_usd', 'balance_eur'])) {
                        $currentBalance = $customer->$currencyField ?? 0;
                        $newBalance = $currentBalance + $totalAmount;
                        $customer->$currencyField = $newBalance;
                        
                        // Also update legacy balance field
                        $customer->balance = ($customer->balance ?? 0) + $totalAmount;
                        $customer->save();
                        
                        Log::info('Customer balance updated during bulk delete', [
                            'customer_id' => $customer->id,
                            'collection_id' => $collection->id,
                            'currency' => $collection->currency,
                            'amount' => $collection->amount,
                            'discount' => $discount,
                            'total_added' => $totalAmount,
                            'old_balance' => $currentBalance,
                            'new_balance' => $newBalance
                        ]);
                    }
                }
            }
            
            // Delete collections
            $deletedCount = Collection::whereIn('id', $ids)->delete();
            
            DB::commit();
            
            Log::info('Bulk delete collections completed', [
                'deleted_count' => $deletedCount,
                'requested_ids' => count($ids)
            ]);
            
            return redirect()->route('finance.collections.index')
                ->with('success', $deletedCount . ' tahsilat başarıyla silindi');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk delete error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ids' => $request->input('ids')
            ]);
            return redirect()->back()->with('error', 'Silme işlemi sırasında bir hata oluştu: ' . $e->getMessage());
        }
    }
}
