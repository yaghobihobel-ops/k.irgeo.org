@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="row justify-content-center justify-content-xl-start">
        <div class="col-xxl-6 col-xl-8 col-lg-8">
            <h4 class="mb-4">
                <a href="{{ route('user.mobile.recharge.history') }}">
                    <span class="icon" title="@lang('Recharge History')">
                        <i class="las la-arrow-circle-left"></i>
                    </span>
                    {{ __($pageTitle) }}
                </a>
            </h4>
            <form action="{{ route('user.mobile.recharge.store') }}" method="post"
                class="send-money-form has-otp-form no-submit-loader">
                @csrf
                <div class="card custom--card mb-3">
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form--label">@lang('Mobile Number')</label>
                            <div class="input-group style-three">
                                <input type="number" required class="form--control form-control"
                                    placeholder="@lang('Enter mobile number')" name="mobile_number" value="{{ old('mobile_number') }}"
                                    required>
                            </div>
                            <div class="send-list">
                                @foreach ($latestMobileRecharge as $mobileRecharge)
                                    <div class="send-list-item mt-3 suggest-data" data-name="mobile_number">
                                        <span class="icon">
                                            <i class="fa-solid fa-user-large"></i>
                                        </span>
                                        <span class="number data">{{ @$mobileRecharge->mobile }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="d-flex gap-1 justify-content-between mb-2 flex-wrap">
                                <label class="form--label">@lang('Enter Amount')</label>
                                <span>
                                    @lang('Available Balance'):
                                    {{ gs('cur_sym') }}{{ showAmount(auth()->user()->balance, currencyFormat: false) }}
                                </span>
                            </div>
                            <div class="input-group input--amount border-0">
                                <input type="number" step="any" class="form--control sm-style form-control"
                                    placeholder="@lang('0.00')" value="{{ old('amount') }}" name="amount" required>
                                <span class="input-group-text">{{ __(gs('cur_text')) }}</span>
                            </div>
                            <div class="flex-align gap-2 mt-3">
                                @foreach (gs('quick_amounts') ?? [] as $amount)
                                    <span class="suggest-amount quick-amount" data-amount="{{ getAmount($amount) }}">
                                        {{ gs('cur_sym') }}{{ showAmount($amount, currencyFormat: false) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form--label">@lang('Operator')</label>
                            <div class="single-operator-wrapper justify-content-start">
                                @foreach ($operators as $operator)
                                    <label for="{{ $operator->id }}" class="single-operator"
                                        data-fixed-charge="{{ $operator->fixed_charge }}"
                                        data-percent-charge="{{ $operator->percent_charge }}">
                                        <input id="{{ $operator->id }}" type="radio" name="operator"
                                            value="{{ $operator->id }}">
                                        <span class="img">
                                            <img src="{{ getImage(getFilePath('mobile_operator') . '/' . $operator->image) }}"
                                                alt="">
                                        </span>
                                        <span class="title">{{ __(@$operator->name) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <input type="hidden" class="fixed-charge">
                        <input type="hidden" class="percent-charge">
                        <x-otp_verification remark="mobile_recharge" />
                    </div>
                </div>
                <div class="card custom--card mb-3">
                    <div class="card-body">
                        <div class="deposit-wrapper">
                            <div class="deposit-wrapper-info">
                                <span class="title">
                                    @lang('Processing Charge')
                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                        class="charge-info" data-bs-title="@lang('Processing Charge')">
                                        <i class="las la-info-circle"></i>
                                    </button>
                                </span>
                                <span class="amount">
                                    <span>{{ gs('cur_sym') }}</span><span class="processing-fee">@lang('0.00')</span>
                                </span>
                            </div>
                            <div class="deposit-wrapper-total">
                                <span class="title">
                                    @lang('Total')
                                </span>
                                <span class="amount">
                                    <span>{{ gs('cur_sym') }}</span><span class="final-amount">@lang('0.00')</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn--base text-start w-100">
                    <span class="flex-between">
                        @lang('Continue')
                        <span class="icon">
                            <i class="fas fa-arrow-right-long"></i>
                        </span>
                    </span>
                </button>

            </form>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";
        (function($) {

            $('.single-operator').on('click', function() {

                let percentCharge = parseFloat($(this).data('percent-charge')) || 0;
                let fixedCharge = parseFloat($(this).data('fixed-charge')) || 0;

                if (percentCharge > 0 || fixedCharge > 0) {
                    percentCharge = percentCharge;
                    fixedCharge = fixedCharge;
                } else {
                    percentCharge = parseFloat("{{ $mobileRechargeCharge->percent_charge }}") || 0;
                    fixedCharge = parseFloat("{{ $mobileRechargeCharge->fixed_charge }}") || 0;
                }

                $('.fixed-charge').val(fixedCharge);
                $('.percent-charge').val(percentCharge);

                $('.charge-info').attr('data-bs-title',
                        `Charge: {{ gs('cur_sym') }}${fixedCharge.toFixed(2)}+${percentCharge.toFixed(0)}%`)
                    .tooltip(
                        'dispose')
                    .tooltip();

                calculation();

            });

            let percentCharge = parseFloat("{{ $mobileRechargeCharge->percent_charge }}" || 0);
            let fixedCharge = parseFloat("{{ $mobileRechargeCharge->fixed_charge }}" || 0);


            $("input[name=amount]").on('input', function() {
                calculation();
            });
            $(".quick-amount").on('click', function() {
                $("input[name=amount]").val(parseInt($(this).data("amount")));
                calculation();
            });

            function calculation() {

                let percentCharge = parseFloat($('.percent-charge').val() || 0);
                let fixedCharge = parseFloat($('.fixed-charge').val() || 0);

                if (percentCharge > 0 || fixedCharge > 0) {
                    percentCharge = percentCharge;
                    fixedCharge = fixedCharge;
                } else {
                    percentCharge = parseFloat("{{ $mobileRechargeCharge->percent_charge }}") || 0;
                    fixedCharge = parseFloat("{{ $mobileRechargeCharge->fixed_charge }}") || 0;
                }

                const amount = parseFloat($('body').find(`input[name="amount"]`).val() || 0);
                const totalPercentCharge = parseFloat(amount / 100 * percentCharge);
                const totalCharge = parseFloat(totalPercentCharge + fixedCharge);
                const totalAmount = parseFloat(amount + totalPercentCharge + fixedCharge);
                $(".processing-fee").text(totalCharge.toFixed(2));
                $(".final-amount").text(totalAmount.toFixed(2));
            }

            $(".send-list-item").on("click", function() {
                var number = $(this).find(".number").text().trim();
                $(".mobile").val(number);
            });

            $('.breadcrumb-plugins-wrapper').remove();

            $(".suggest-data").on('click', function() {
                const $this = $(this);
                $('body').find(`input[name=${$this.data('name')}]`).val($this.find('.data').text().trim());
            });
        })(jQuery);
    </script>
@endpush
