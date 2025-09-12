<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::withCount('users')->orderBy('created_at', 'desc')->get();
        return view('management.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $availablePermissions = [
            'dashboard' => 'Dashboard',
            'sales' => 'Satışlar',
            'purchases' => 'Alışlar', 
            'products' => 'Ürünler',
            'finance' => 'Finans',
            'expenses' => 'Giderler',
            'reports' => 'Raporlar',
            'management' => 'Yönetim'
        ];
        
        return view('management.roles.create', compact('availablePermissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles'],
            'display_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string'],
        ]);

        Role::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'permissions' => $request->permissions,
        ]);

        return redirect()->route('management.roles.index')
                        ->with('success', 'Rol başarıyla oluşturuldu.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $role->load('users');
        return view('management.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $availablePermissions = [
            'dashboard' => 'Dashboard',
            'sales' => 'Satışlar',
            'purchases' => 'Alışlar',
            'products' => 'Ürünler', 
            'finance' => 'Finans',
            'expenses' => 'Giderler',
            'reports' => 'Raporlar',
            'management' => 'Yönetim'
        ];
        
        return view('management.roles.edit', compact('role', 'availablePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
            'display_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string'],
        ]);

        // Prevent modification of god_mode role permissions
        if ($role->name === 'god_mode') {
            $request->merge(['permissions' => ['*']]);
        }

        $role->update([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'permissions' => $request->permissions,
        ]);

        return redirect()->route('management.roles.index')
                        ->with('success', 'Rol başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // Prevent deletion of system roles
        if (in_array($role->name, ['god_mode', 'admin', 'employee'])) {
            return redirect()->route('management.roles.index')
                            ->with('error', 'Sistem rolleri silinemez.');
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return redirect()->route('management.roles.index')
                            ->with('error', 'Bu role sahip kullanıcılar bulunmaktadır. Önce kullanıcıları başka rollere atayın.');
        }

        $role->delete();

        return redirect()->route('management.roles.index')
                        ->with('success', 'Rol başarıyla silindi.');
    }
}