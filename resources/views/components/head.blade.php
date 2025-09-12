<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('company.name') }} - Hesap Takip Sistemi</title>
    <link rel="icon" type="image/png"  href="{{ asset('assets/images/logo-icon.png') }}" sizes="16x16">
    <!-- remix icon font css  -->
    <link rel="stylesheet"  href="{{ asset('assets/css/remixicon.css') }}">
    <!-- BootStrap css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/bootstrap.min.css') }}">
    <!-- Apex Chart css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/apexcharts.css') }}">
    <!-- Data Table css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/dataTables.min.css') }}">
    <!-- DataTables Responsive css -->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    
    <!-- Custom Responsive DataTable CSS -->
    <style>
        /* Improve mobile DataTable experience */
        @media screen and (max-width: 767px) {
            .dataTables_wrapper {
                width: 100%;
                overflow-x: hidden;
            }
            
            .table-responsive {
                border: none;
            }
            
            table.dataTable.dtr-inline.collapsed > tbody > tr > td.control:before {
                background-color: #007bff;
                color: white;
                border-radius: 3px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            table.dataTable.dtr-inline.collapsed > tbody > tr.parent > td.control:before {
                background-color: #dc3545;
            }
            
            .dtr-details {
                background-color: #f8f9fa;
                border: 1px solid #dee2e6;
                border-radius: 5px;
                padding: 10px;
                margin: 5px 0;
            }
            
            .dtr-details li {
                padding: 5px 0;
                border-bottom: 1px solid #e9ecef;
            }
            
            .dtr-details li:last-child {
                border-bottom: none;
            }
            
            .dtr-title {
                font-weight: 600;
                color: #495057;
                min-width: 100px;
                display: inline-block;
            }
            
            .dtr-data {
                color: #212529;
            }
        }
        
        /* DataTables Styling - Template Style */
        .dataTables_wrapper {
            margin-top: 0;
        }
        
        .dataTables_wrapper .dataTables_length {
            float: left;
            margin-bottom: 20px;
        }
        
        .dataTables_wrapper .dataTables_length select {
            padding: 8px 12px;
            border: 1px solid #e1e5e9;
            border-radius: 6px;
            background-color: #fff;
            color: #495057;
            font-size: 14px;
        }
        
        .dataTables_wrapper .dataTables_filter {
            float: right;
            margin-bottom: 20px;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            padding: 8px 12px;
            border: 1px solid #e1e5e9;
            border-radius: 6px;
            background-color: #fff;
            color: #495057;
            font-size: 14px;
            margin-left: 8px;
        }
        
        .dataTables_wrapper .dataTables_info {
            float: left;
            padding-top: 12px;
            color: #6c757d;
            font-size: 14px;
        }
        
        .dataTables_wrapper .dataTables_paginate {
            float: right;
            margin: 0;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            display: inline-block;
            padding: 8px 12px;
            margin-left: 4px;
            text-decoration: none;
            border: 1px solid #e1e5e9;
            border-radius: 6px;
            color: #495057;
            background-color: #fff;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.15s ease-in-out;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            color: #495057;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background-color: #487fff;
            border-color: #487fff;
            color: #fff;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background-color: #3d6dff;
            border-color: #3d6dff;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            color: #adb5bd;
            cursor: not-allowed;
            background-color: #fff;
            border-color: #e1e5e9;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
            background-color: #fff;
            border-color: #e1e5e9;
            color: #adb5bd;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.previous,
        .dataTables_wrapper .dataTables_paginate .paginate_button.next {
            font-weight: 500;
        }
        
        /* Clear floats after pagination */
        .dataTables_wrapper .dataTables_info:after,
        .dataTables_wrapper .dataTables_paginate:after {
            content: "";
            display: table;
            clear: both;
        }
        
        /* Bottom wrapper styling */
        .dataTables_wrapper .bottom {
            margin-top: 20px;
            border-top: 1px solid #e1e5e9;
            padding-top: 20px;
        }
        
        /* Ensure tables don't overflow on mobile */
        .table-responsive .table {
            margin-bottom: 0;
        }
        
        /* Allow horizontal scroll on mobile for tables */
        @media screen and (max-width: 767px) {
            .table-responsive {
                overflow-x: auto !important;
                border: none;
                -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
            }
            
            .dataTables_scrollBody {
                overflow-x: auto !important;
            }
            
            .dataTables_wrapper .dataTables_scroll {
                overflow-x: auto !important;
            }
            
            /* Allow table to be wider than container for scrolling */
            .table-responsive table {
                min-width: 100%;
                white-space: nowrap;
            }
            
            /* Make columns more readable on mobile */
            .table-responsive th,
            .table-responsive td {
                min-width: 120px;
                padding: 8px 12px;
            }
            
            /* Smaller padding for checkbox column */
            .table-responsive th:first-child,
            .table-responsive td:first-child {
                min-width: 60px;
                padding: 8px 6px;
            }
        }

        /* Critical Stock Badge Hover Effect */
        .critical-stock-badge {
            transition: all 0.2s ease-in-out !important;
            cursor: pointer !important;
            position: relative !important;
            overflow: hidden !important;
        }

        .critical-stock-badge:hover {
            transform: scale(1.05) !important;
            background-color: #b02a37 !important;
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3) !important;
            animation: pulse 0.6s ease-in-out !important;
        }

        .critical-stock-badge:active {
            transform: scale(0.98) !important;
        }

        /* Responsive Form Improvements */
        @media screen and (max-width: 768px) {
            /* Form responsive improvements */
            .form-control {
                font-size: 16px; /* Prevent zoom on iOS */
            }
            
            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }
            
            /* Card responsive */
            .card-body {
                padding: 1rem;
            }
            
            /* Icon field responsive */
            .icon-field .icon {
                display: none; /* Hide icons on mobile to save space */
            }
            
            /* Badge responsive */
            .badge {
                font-size: 0.7rem;
                padding: 0.25rem 0.5rem;
            }
            
            /* Alert responsive */
            .alert {
                padding: 0.75rem;
                font-size: 0.875rem;
            }
            
            /* Dashboard cards responsive */
            .card-body p {
                font-size: 0.75rem;
            }
            
            .card-body h6 {
                font-size: 1rem;
            }
        }
        
        @media screen and (max-width: 576px) {
            /* Extra small screens */
            .container-fluid {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
            
            .card {
                margin-bottom: 0.5rem;
            }
            
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
            
            /* Form labels */
            .form-label {
                font-size: 0.875rem;
                margin-bottom: 0.25rem;
            }
            
            /* Input groups */
            .input-group {
                flex-wrap: wrap;
            }
            
            .input-group .form-control {
                margin-bottom: 0.5rem;
            }
            
            /* Modal responsive */
            .modal-dialog {
                margin: 0.5rem;
                max-width: calc(100% - 1rem);
            }
            
            .modal-content {
                border-radius: 0.5rem;
            }
            
            .modal-body {
                padding: 1rem;
            }
            
            .modal-header {
                padding: 0.75rem 1rem;
            }
            
            .modal-footer {
                padding: 0.75rem 1rem;
            }
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }
    </style>
    <!-- Text Editor css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/editor-katex.min.css') }}">
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/editor.atom-one-dark.min.css') }}">
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/editor.quill.snow.css') }}">
    <!-- Date picker css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/flatpickr.min.css') }}">
    <!-- Calendar css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/full-calendar.css') }}">
    <!-- Vector Map css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/jquery-jvectormap-2.0.5.css') }}">
    <!-- Popup css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/magnific-popup.css') }}">
    <!-- Slick Slider css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/slick.css') }}">
    <!-- prism css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/prism.css') }}">
    <!-- file upload css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/file-upload.css') }}">

    <link rel="stylesheet"  href="{{ asset('assets/css/lib/audioplayer.css') }}">
    <!-- main css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/style.css') }}">
</head>