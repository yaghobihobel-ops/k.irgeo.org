@extends('admin.layouts.app')
@section('panel')
    <div class="submitRequired form-change-alert d-none">
        <i class="fas fa-exclamation-triangle"></i>
        @lang('You\'ve to click on the submit button to apply the changes')
    </div>
    <div class="row">
        <div class="col-12">
            <form method="post" action="{{ route('admin.utility.bill.company.configure', $utility->id) }}">
                @csrf
                <x-generated-form :form=$form generateTitle="{{ $utility->name }} Bill Configuration"
                    formTitle="Adjust Settings for {{ $utility->name }} Bill" :randerbtn=true />
            </form>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-back_btn route="{{ route('admin.utility.bill.company.all') }}" />
@endpush
