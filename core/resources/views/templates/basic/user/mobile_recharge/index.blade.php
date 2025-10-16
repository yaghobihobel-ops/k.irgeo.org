@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="card custom--card">
        <div class="card-header">
            <form class="table-search no-submit-loader">
                <input type="search" name="search" class="form-control form--control" value="{{ request()->search }}"
                    placeholder="@lang('Search...')">
                <button class="icon px-3" type="submit">
                    <i class="fa fa-search"></i>
                </button>
            </form>
        </div>
        <div class="card-body p-0">
            <table class="table table--responsive--xl">
                <thead>
                    <tr>
                        <th>@lang('Mobile Number') | @lang('Operator')</th>
                        <th>@lang('Trx') | @lang('Time')</th>
                        <th>@lang('Amount') | @lang('Charge')</th>
                        <th>@lang('Total Amount')</th>
                        <th>@lang('Status')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recharges as $mobileRecharge)
                        <tr>
                            <td>
                                <div>
                                    <span class="d-block">
                                        +<span>{{ @$mobileRecharge->dial_code }}{{ @$mobileRecharge->mobile }}</span>
                                    </span>
                                    <span class="d-block">
                                        {{ __(@$mobileRecharge->mobileOperator->name) }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <span class="d-block">{{ $mobileRecharge->trx }}</span>
                                    <span title="{{ diffForHumans($mobileRecharge->created_at) }}">
                                        {{ showDateTime($mobileRecharge->created_at) }}</span>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <span class="d-block">{{ showAmount($mobileRecharge->amount) }}</span>
                                    <span class="text--success" title="@lang('Charge')">
                                        {{ showAmount($mobileRecharge->charge) }}</span>
                                </div>
                            </td>
                            <td>
                                {{ showAmount($mobileRecharge->total) }}
                            </td>
                            <td>@php echo $mobileRecharge->statusBadge @endphp</td>
                        </tr>
                    @empty
                        @include('Template::partials.empty_message')
                    @endforelse
                </tbody>
            </table>
            @if ($recharges->hasPages())
                {{ paginateLinks($recharges) }}
            @endif
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('user.mobile.recharge.create') }}" class="btn btn--base btn--md">
        <span class="icon"><i class="fa fa-plus-circle"></i></span>
        @lang('New Recharge')
    </a>
@endpush
