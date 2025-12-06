@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Create New Order Request</h4>
                    <a href="{{ route('restock-requests.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>

                <div class="card-body">
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Direct restock creation is disabled. Use <strong>Department Carts</strong> to add approved supply requests and finalize the cart to generate restock orders.
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('restock-requests.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Back to List
                        </a>
                        <a href="{{ route('departments.index') }}" class="btn btn-primary">
                            <i class="fas fa-building"></i> Go to Departments
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection