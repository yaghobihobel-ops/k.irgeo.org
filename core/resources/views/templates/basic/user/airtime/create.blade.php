@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="row justify-content-center justify-content-xl-start">
        <div class="col-xxl-6 col-xl-8 col-lg-8">
            <h4 class="mb-4">
                <a href="{{ route('user.airtime.history') }}">
                    <span class="icon" title="@lang('Airtime History')">
                        <i class="las la-arrow-circle-left"></i>
                    </span>
                    {{ __($pageTitle) }}
                </a>
            </h4>
            <form action="{{ route('user.airtime.store') }}" method="post"
                class="send-money-form has-otp-form no-submit-loader">
                @csrf
                <div class="card custom--card mb-3">
                    <div class="card-body">
                        <div class="form--group form-group">
                            <label class="form--label">@lang('Country')</label>
                            <select class="form-control  img-select2" data-minimum-results-for-search="-1" name="country">
                                <option value="">@lang('Select Country')</option>
                                @foreach ($countries as $country)
                                    <option value='{{ $country->id }}' data-country='@json($country)'
                                        data-src="{{ $country->flag_url }}" @selected($loop->first)>
                                        {{ __($country->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form--group form-group">
                            <label class="form--label required">@lang('Operator')</label>
                            <input type="hidden" name="operator" class="operator-id">
                            <div class="select-operator">
                                <div class="left">
                                    <div class="thumb">
                                        <img src="">
                                    </div>
                                    <span class="title"></span>
                                </div>
                                <div class="btn-wrapper">
                                    <button type="button" class="btn btn--base btn--sm" data-bs-toggle="modal"
                                        data-bs-target="#operator-modal">
                                        @lang('Change')
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form--label">@lang('Mobile Number')</label>
                            <div class="input-group style-three">
                                <span class="input-group-text">
                                    <span class="dial-code"></span>
                                </span>
                                <input type="number" required class="form--control form-control"
                                    placeholder="@lang('Mobile number')" name="mobile_number" value="{{ old('mobile_number') }}"
                                    required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="d-flex gap-1 justify-content-between">
                                <label class="form--label">@lang('Enter Amount')</label>
                                <span class="text--danger amount-limit d-none">
                                    <span class="amount"></span>
                                    <span>{{ __(gs('cur_text')) }}</span>
                                </span>
                            </div>
                            <div class="input-group input--amount border-0">
                                <input type="number" step="any" class="form--control sm-style form-control"
                                    placeholder="@lang('0.00')" value="{{ old('amount') }}" name="amount" required>
                                <span class="input-group-text">{{ __(gs('cur_text')) }}</span>
                            </div>
                            <div class="flex-align gap-2 mt-3 suggest-amount-wrapper">
                                @foreach (gs('quick_amounts') ?? [] as $amount)
                                    <span class="suggest-amount quick-amount" data-amount="{{ getAmount($amount) }}">
                                        {{ gs('cur_sym') }}{{ showAmount($amount, currencyFormat: false) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <x-otp_verification remark="air_time" />
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

    <div class="modal custom--modal fade" id="operator-modal">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h4 class="modal-title">@lang('Select Operator')</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs custom--tab modify-with-operator" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active operator-filter-btn" type="button" data-show="all">
                                @lang('All')
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link operator-filter-btn" type="button" data-show="data">
                                @lang('Data')
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link operator-filter-btn" type="button" data-show="bundle">
                                @lang('Bundle')
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link operator-filter-btn" type="button" data-show="pin">
                                @lang('PIN')
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="single-operator-wrapper"></div>
                    </div>
                </div>
                <div class="modal-footer justify-content-center border-0 modify-with-operator">
                    <button type="button" class="btn btn--base operator-confirm">
                        <i class="las la-check-circle"></i> @lang('Confirm')
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('script')
    <script>
        "use strict";
        (function($) {

            const $operatorModal = $("#operator-modal");
            const $operatorWrapper = $operatorModal.find(".single-operator-wrapper");
            const $selectOperatorWrapper = $('body').find(".select-operator");
            const $suggestAmountWrapper = $('body').find(".suggest-amount-wrapper");
            const operators = [];
            let selectedOperatorIndex=0;

            $(".send-list-item").on("click", function() {
                var number = $(this).find(".number").text().trim();
                $(".mobile").val(number);
            });

            $('.breadcrumb-plugins-wrapper').remove();
            $(".suggest-data").on('click', function() {
                const $this = $(this);
                $('body').find(`input[name=${$this.data('name')}]`).val($this.find('.data').text().trim());
            });

            function formatState(state) {
                if (!state.id) {
                    return state.text;
                }
                var $state = $(
                    '<span class="img-flag-inner"><img src="' + $(state.element).attr('data-src') +
                    '" class="img-flag" /> ' + state.text + '</span>'
                );
                return $state;
            };

            $('.img-select2').select2({
                templateResult: formatState,
                templateSelection: formatState,
                width: "100%"
            });

            $("select[name=country]").on('change', function() {
                const country = $(this).find('option:selected').data('country') ?? null;
                $(".dial-code").text(country.calling_codes[0]);

                selectedOperatorIndex=0;
                operators.length = 0;
                operators.push(...country.operators);

                operatorHtml();
                setSingleOperatorHtml(selectedOperatorIndex);
            }).change();

            $("body").on('click', ".operator-filter-btn", function() {
                $('.operator-filter-btn').removeClass('active');
                $(this).addClass('active');

                if ($(this).data('show') == 'all') {
                    $operatorWrapper.find('.single-operator').removeClass('d-none');
                } else {
                    $operatorWrapper.find('.single-operator').addClass('d-none');
                    $operatorWrapper.find(`.${$(this).data('show')}-1`).removeClass('d-none');
                }
            });

            $("body").on('click', ".operator-confirm", function() {
                const $selectedOperator = $operatorModal.find('.single-operator.active');
                if ($selectedOperator.length <= 0) {
                    notify("error", "Please select the operator");
                    return;
                }
                selectedOperatorIndex= $selectedOperator.data('index');
                setSingleOperatorHtml();
                $operatorModal.modal('hide');
            });

            $("body").on('click', ".single-operator", function() {
                $(this).addClass('active').siblings().removeClass('active');
            });


            function operatorHtml() {
                let html = ``;
                if (operators.length > 0) {
                    operators.forEach((operator,index) => {
                        html += `<label for="${operator.id}" class="single-operator data-${operator.data} bundle-${operator.bundle} pin-${operator.pin}" data-index="${index}">
                        <input id="${operator.id}" type="radio" name="operator">
                        <span class="img">
                            <img src="${operator.logo_urls[0]}" alt="">
                            </span>
                        <span class="title">${operator.name}</span>
                    </label>`;
                    });
                    $(".modify-with-operator").removeClass('d-none');

                } else {
                    html = `
                    <div class="empty-message">
                        <p class="empty-message-icon">
                            <img src="{{ asset('assets/images/no-data.gif') }}" alt="">
                        </p>
                        <p class="empty-message-text">
                        </p>
                    </div>
                    `
                    $(".modify-with-operator").addClass('d-none');


                }
                $operatorWrapper.html(html);
            }

            $("body").on('click', ".quick-amount", function() {
                $("input[name=amount]").val(parseFloat($(this).data("amount")).toFixed(2));
            });

            function setSingleOperatorHtml() {
                const selectedOperator = operators[selectedOperatorIndex];

                if (selectedOperator != undefined && Object.keys(selectedOperator).length > 0) {
                    $selectOperatorWrapper.find('.title').text(selectedOperator.name);
                    $selectOperatorWrapper.find('img').attr('src', selectedOperator.logo_urls[0]);
                    $selectOperatorWrapper.find('.thumb,.btn-wrapper').removeClass('d-none');
                    $('body').find('.operator-id').val(selectedOperator.id);

                    if (selectedOperator.max_amount && selectedOperator.min_amount) {
                        $(".amount-limit").removeClass('d-none');
                        $(".amount-limit").find('.amount').text(
                            `${parseFloat(selectedOperator.min_amount).toFixed(2)}-${parseFloat(selectedOperator.max_amount).toFixed(2)}`
                        );
                    } else {
                        $(".amount-limit").addClass('d-none');
                    }
                    var amountHtml = ``;
                    if (selectedOperator.suggested_amounts && selectedOperator.suggested_amounts.length > 0) {
                        selectedOperator.suggested_amounts.forEach(amount => {
                            amountHtml += `<span class="suggest-amount quick-amount" data-amount="${parseFloat(amount).toFixed(2)}">
                                    {{ gs('cur_sym') }}${parseFloat(amount).toFixed(2)}
                            </span>
                            `
                        });
                    }else if(selectedOperator.fixed_amounts && selectedOperator.fixed_amounts.length > 0) {
                        if (selectedOperator.fixed_amounts_descriptions && Object.keys(selectedOperator.fixed_amounts_descriptions).length > 0) {
                            Object.entries(selectedOperator.fixed_amounts_descriptions).forEach(([key, value]) => {
                            amountHtml += `<span class="suggest-amount quick-amount" data-amount="${parseFloat(key).toFixed(2)}">
                                <div class="d-flex gap-1 justify-content-center align-items-center flex-column">
                                    {{ gs('cur_sym') }}${parseFloat(key).toFixed(2)}
                                    <p class="fs-14">${value}</p>
                                    </div>
                            </span>`;
                            
                        });
                        }else{
                            selectedOperator.fixed_amounts.forEach(amount => {
                            amountHtml += `<span class="suggest-amount quick-amount" data-amount="${parseFloat(amount).toFixed(2)}">
                                {{ gs('cur_sym') }}${parseFloat(amount).toFixed(2)}
                            </span>
                            `
                        });
                        }
                      
                    }else {
                        amountHtml += `@foreach (gs('quick_amounts') ?? [] as $amount)
                            <span class="suggest-amount quick-amount" data-amount="{{ getAmount($amount) }}">
                                {{ gs('cur_sym') }}{{ showAmount($amount, currencyFormat: false) }}
                            </span>
                        @endforeach`
                    }
                    $suggestAmountWrapper.html(amountHtml);
                } else {
                    $selectOperatorWrapper.find('.title').text("@lang('No Operator found')");
                    $selectOperatorWrapper.find('.thumb,.btn-wrapper').addClass('d-none');
                    $(".amount-limit").addClass('d-none');
                    $('body').find('.operator-id').val("");
                    var amountHtml = ``;
                    amountHtml += `@foreach (gs('quick_amounts') ?? [] as $amount)
                            <span class="suggest-amount quick-amount" data-amount="{{ getAmount($amount) }}">
                                {{ gs('cur_sym') }}{{ showAmount($amount, currencyFormat: false) }}
                            </span>
                        @endforeach`
                    $suggestAmountWrapper.html(amountHtml);
                }
            }
        })(jQuery);
    </script>
@endpush
