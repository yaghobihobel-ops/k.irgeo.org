@extends($activeTemplate . 'layouts.master')
@section('content')
    <h4 class="mb-4">
        <a href="{{ route('user.utility.bill.history') }}">
            <span class="icon" title="@lang('Bill History')">
                <i class="las la-arrow-circle-left"></i>
            </span>
            {{ __(@$pageTitle) }}
        </a>
    </h4>
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="bill-type-wrapper mb-4">
                @foreach ($billCategory as $category)
                    <div class="bill-type bg-white" data-category-id="{{ $category->id }}">
                        <div class="thumb bg-base">
                            <img src="{{ getImage(getFilePath('utility') . '/' . @$category->image) }}" alt="">
                        </div>
                        <span class="text">{{ __(@$category->name) }}</span>
                    </div>
                @endforeach
            </div>
            <form action="{{ route('user.utility.bill.store') }}" method="post"
                class="send-money-form has-otp-form no-submit-loader">
                @csrf
                <div class="short-wrapper mx-0">
                    <div class="row gy-4">
                        <div class="col-md-6">
                            <div class="card custom--card p-0 overflow-hidden mb-4">
                                <div class="card-header p-3 p-sm-4">
                                    <div class="table-search">
                                        <input type="text" class="form--control search-biller"
                                            placeholder="@lang('Search for biller')">
                                        <button class="icon" type="button">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none">
                                                <path
                                                    d="M11 19C15.4183 19 19 15.4183 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11C3 15.4183 6.58172 19 11 19Z"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                                <path d="M20.9992 21.0002L16.6992 16.7002" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card custom--card p-0 overflow-hidden">
                                <div class="card-body px-3 px-sm-4">
                                    @if ($userCompanies->count())
                                        <h6 class="mt-3">@lang('Saved Accounts')</h6>
                                    @endif
                                    <div class="bill-select-wrapper">
                                        @foreach ($userCompanies as $company)
                                            <div class="bill-select-item">
                                                <div class="bill-select d-flex justify-content-between flex-wrap  ">
                                                    <span class="left flex-fill">
                                                        <span class="thumb">
                                                            <img src="{{ getImage(getFilePath('utility') . '/' . @$company->company->image) }}"
                                                                alt="image">
                                                        </span>
                                                        <span class="content">
                                                            <span class="title">{{ __(@$company->company->name) }}</span>
                                                            <span>{{ __(@$company->unique_id) }}</span>
                                                        </span>

                                                    </span>
                                                    <div
                                                        class="right d-flex gap-1 flex-wrap flex-fill justify-content-start justify-content-xxl-end">
                                                        <button type="button"
                                                            class="btn btn-outline--danger btn--sm confirmationBtn"
                                                            data-question='@lang('Are you sure delete this company?')'
                                                            data-action="{{ route('user.utility.bill.company.delete', $company->id) }}">
                                                            <i class="fa fa-times"></i>
                                                            @lang('Remove')
                                                        </button>
                                                        <button type="button"
                                                            data-biller-name="{{ __($company->company->name) }}"
                                                            data-biller-image="{{ getImage(getFilePath('utility') . '/' . $company->company->image) }}"
                                                            data-category-id="{{ $company->company->category_id }}"
                                                            data-category-name="{{ __(@$company->company->category->name) }}"
                                                            data-form-id="{{ __(@$company->company->form_id) }}"
                                                            data-fixed-charge="{{ $company->company->fixed_charge }}"
                                                            data-percent-charge="{{ $company->company->percent_charge }}"
                                                            data-user-account="{{ $company }}"
                                                            class="btn btn--light btn--sm select-company-account">
                                                            <i class="fa fa-paper-plane"></i> @lang('Pay')
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        @if ($companies->count())
                                            <p class="fs-20 mt-3 fw-bold text-dark">@lang('All Billers')</p>
                                        @endif
                                        @forelse($companies as $company)
                                            <div class="bill-select-item biller-item"
                                                data-category-id="{{ @$company->category->id }}"
                                                data-biller-name="{{ __($company->name) }}">
                                                <div class="bill-select d-flex justify-content-between flex-wrap  ">
                                                    <span class="left flex-fill">
                                                        <span class="thumb">
                                                            <img src="{{ getImage(getFilePath('utility') . '/' . @$company->image) }}"
                                                                alt="">
                                                        </span>
                                                        <span class="content">
                                                            <span class="title">{{ __(@$company->name) }}</span>
                                                            <span>{{ __(@$company->category->name) }}</span>
                                                        </span>
                                                    </span>
                                                    <div
                                                        class="right d-flex gap-1 flex-wrap flex-fill justify-content-start justify-content-xxl-end">
                                                        <button type="button"
                                                            class="right btn btn--light btn--sm add-account"
                                                            data-company="{{ $company }}"
                                                            data-form-id="{{ $company->form_id }}">
                                                            <i class="fa fa-user-plus"></i> @lang('Save Account')
                                                        </button>
                                                        <button type="button" data-biller-name="{{ __($company->name) }}"
                                                            data-biller-image="{{ getImage(getFilePath('utility') . '/' . $company->image) }}"
                                                            data-biller-id="{{ $company->id }}"
                                                            data-category-id="{{ $company->category_id }}"
                                                            data-category-name="{{ __(@$company->category->name) }}"
                                                            data-form-id="{{ __(@$company->form_id) }}"
                                                            data-fixed-charge="{{ $company->fixed_charge }}"
                                                            data-percent-charge="{{ $company->percent_charge }}"
                                                            class="btn btn--light btn--sm bill-select-item-btn">
                                                            <i class="fa fa-paper-plane"></i> @lang('Pay Now')
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            @include('Template::partials.empty_message')
                                        @endforelse
                                    </div>
                                    <div class="no-results">
                                        @include('Template::partials.empty_message')
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card custom--card mb-3 biller-details">
                                <div class="card-body">
                                    <h5 class="mb-3">@lang('Biller')</h5>
                                    <div class="bill-select select">
                                        <span class="left">
                                            <span class="thumb">
                                                <img class="biller-image" src="" alt="img">
                                            </span>
                                            <span class="content">
                                                <span class="biller-title title"></span>
                                                <span class="biller-category-name"></span>
                                            </span>
                                        </span>
                                    </div>
                                    <input type="hidden" name="user_company_id" class="user-company-id">
                                    <input type="hidden" class="fixed-charge">
                                    <input type="hidden" class="percent-charge">
                                </div>
                            </div>
                            <div class="card custom--card mb-3">
                                <div class="card-body">
                                    <div class="user-company-details"></div>
                                    <div class="form--group">
                                        <label class="form--label">@lang('Enter Amount')</label>
                                        <div class="input-group input--amount border-0">
                                            <input type="number" class="form--control sm-style form-control"
                                                placeholder="@lang('0.00')" value="{{ old('amount') }}"
                                                name="amount" required>
                                            <span class="input-group-text">{{ __(gs('cur_text')) }}</span>
                                        </div>
                                        <div class="d-flex gap-1 justify-content-between  flex-wrap mt-2">
                                            <span>
                                                @lang('Limit:')
                                                <span>
                                                    {{ gs('cur_sym') }}{{ showAmount($utilityCharge->min_limit, currencyFormat: false) }}
                                                    -
                                                    {{ gs('cur_sym') }}{{ showAmount($utilityCharge->max_limit, currencyFormat: false) }}
                                                </span>
                                            </span>
                                            <span>
                                                @lang('Available Balance'):
                                                {{ gs('cur_sym') }}{{ showAmount(auth()->user()->balance, currencyFormat: false) }}
                                            </span>
                                        </div>
                                    </div>
                                    <x-otp_verification remark="utility_bill" />
                                </div>
                            </div>

                            <div class="card custom--card mb-3">
                                <div class="card-body">
                                    <div class="deposit-wrapper">
                                        <div class="deposit-wrapper-info">
                                            <span class="title">
                                                @lang('Processing Charge')
                                                <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                                    data-bs-title="@lang('Processing Charge')" class="charge-info">
                                                    <i class="las la-info-circle"></i>
                                                </button>
                                            </span>
                                            <span class="amount">
                                                <span>{{ gs('cur_sym') }}</span><span
                                                    class="processing-fee">@lang('0.00')</span>
                                            </span>
                                        </div>
                                        <div class="deposit-wrapper-total">
                                            <span class="title">
                                                @lang('Total')
                                            </span>
                                            <span class="amount">
                                                <span>{{ gs('cur_sym') }}</span><span
                                                    class="final-amount">@lang('0.00')</span>
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
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    {{-- company modal --}}
    <div id="company-modal" class="modal fade custom--modal fade modal-lg" tabindex="-1" role="dialog"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog  modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title company-name-title">@lang('New Company')</h4>
                    <button type="button" class="btn-close close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form method="POST" action="{{ route('user.utility.bill.company.store') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="company_id" class="company_id">
                        <div class="form-group">
                            <label class="form--label">@lang('Unique ID')</label>
                            <input type="text" class="form--control" name="unique_id" required>
                            <small class="fs-13">
                                <i>@lang('Set a unique identifier to recognize your account during payment bill.')</i>
                            </small>
                        </div>
                        <div class="company-form"></div>
                        <div class="form-group">
                            <button type="submit" class="btn btn--base btn--md w-100">
                                <i class="fa fa-paper-plane"></i> @lang('Save Now')
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <x-confirmation-modal :isFrontend="true" />
@endsection
@push('style')
    <style>
        .biller-details {
            display: none;
        }

        .bill-type-wrapper .bill-type {
            cursor: pointer;
        }

        .bill-type.active {
            border: 1px solid hsl(var(--base));

        }

        .no-results {
            display: none;
        }
    </style>
@endpush

@push('script')
    <script>
        "use strict";
        (function($) {

            const $loader = $('.full-page-loader');


            $('.bill-select-item-btn').on('click', function() {

                var billerName = $(this).data('biller-name');
                var billerImage = $(this).data('biller-image');
                var billerCategory = $(this).data('category-name');
                var billerId = $(this).data('biller-id');
                var formId = $(this).data('form-id');
                var fixedCharge = parseFloat($(this).data('fixed-charge')) || 0;
                var percentCharge = parseFloat($(this).data('percent-charge')) || 0;


                $('.biller-title').text(billerName);
                $('.biller-image').attr('src', billerImage);
                $('.biller-category-name').text(billerCategory);
                $('.fixed-charge').val(fixedCharge);
                $('.percent-charge').val(percentCharge);
                $('input[name=amount]').val('');
                $(".processing-fee").text('0.00');
                $(".final-amount").text('0.00');
                $('.biller-details').show();

                if (percentCharge > 0 || fixedCharge > 0) {
                    percentCharge = percentCharge;
                    fixedCharge = fixedCharge;
                } else {
                    percentCharge = parseFloat("{{ $utilityCharge->percent_charge }}") || 0;
                    fixedCharge = parseFloat("{{ $utilityCharge->fixed_charge }}") || 0;
                }

                $('.charge-info').attr('data-bs-title',
                        `Charge: {{ gs('cur_sym') }}${fixedCharge.toFixed(2)}+${percentCharge.toFixed(0)}%`)
                    .tooltip('dispose')
                    .tooltip();

                getAccountForm(formId, "pay_now", 'no', billerId);
            });


            $('.select-company-account').on('click', function() {

                var billerName = $(this).data('biller-name');
                var billerImage = $(this).data('biller-image');
                var billerCategory = $(this).data('category-name');
                var userAccount = $(this).data('user-account');
                var fixedCharge = parseFloat($(this).data('fixed-charge')) || 0;
                var percentCharge = parseFloat($(this).data('percent-charge')) || 0;

                $('.biller-title').text(billerName);
                $('.biller-image').attr('src', billerImage);
                $('.biller-category-name').text(billerCategory);
                $('.user-company-id').val(userAccount.id);
                $('.fixed-charge').val(fixedCharge);
                $('.percent-charge').val(percentCharge);
                $('input[name=amount]').val('');
                $(".processing-fee").text('0.00');
                $(".final-amount").text('0.00');
                $('.biller-details').show();

                if (percentCharge > 0 || fixedCharge > 0) {
                    percentCharge = percentCharge;
                    fixedCharge = fixedCharge;
                } else {
                    percentCharge = parseFloat("{{ $utilityCharge->percent_charge }}") || 0;
                    fixedCharge = parseFloat("{{ $utilityCharge->fixed_charge }}") || 0;
                }

                $('.charge-info').attr('data-bs-title',
                        `Charge: {{ gs('cur_sym') }}${fixedCharge.toFixed(2)}+${percentCharge.toFixed(0)}%`)
                    .tooltip(
                        'dispose')
                    .tooltip();

                getAccountDetails(userAccount.id);
            });

            function getAccountDetails(userCompanyId) {
                const action = "{{ route('user.utility.bill.company.details', ':id') }}";
                $.ajax({
                    url: action.replace(':id', userCompanyId),
                    type: "GET",
                    beforeSend: function() {
                        $loader.removeClass('d-none');
                    },
                    complete: function() {
                        $loader.addClass('d-none');
                    },
                    success: function(response) {
                        if (response.status === "success" && response.data && response.data.content) {
                            $('.user-company-details').html(response.data.content);
                            calculation();
                        } else {
                            notify('error', response.message || "@lang('Something went wrong')");
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);

                        }
                    }
                });
            }

            var $companyModal = $('#company-modal');

            $('.add-account').on('click', function() {
                const formId = $(this).data('form-id');
                const company = $(this).data('company');
                $companyModal.find('.company_id').val(company.id);
                $companyModal.find('.company-name-title').text(company.name);
                getAccountForm(formId, "add_account", 'yes');
            });

            function getAccountForm(formId, actionType, hideFile, billerId = null) {
                const action = "{{ route('user.utility.bill.form', ':id') }}";

                $.ajax({
                    url: action.replace(':id', formId),
                    type: "GET",
                    data: {
                        hide_file: hideFile
                    },
                    beforeSend: function() {
                        $loader.removeClass('d-none');
                    },
                    complete: function() {
                        $loader.addClass('d-none');
                    },
                    success: function(response) {
                        if (response.status === "success") {
                            if (actionType == 'pay_now') {
                                $('.user-company-details').html(response.data.content);
                                $('.user-company-details').append(
                                    `<input type="hidden" name="company_id" value="${billerId}">`);
                            }
                            if (actionType == 'add_account') {
                                $('.company-form').html(response.data.content);
                                $companyModal.modal('show');
                            }
                        } else {
                            notify('error', response.message || "@lang('Something went wrong')");
                        }
                    }
                });
            }


            $(".bill-type").on("click", function() {
                var selectedCategory = $(this).attr("data-category-id");
                $(".bill-type").removeClass("active");
                $(this).addClass("active");

                $('.search-biller').val('');

                filterBillers();
            });

            $(".search-biller").on("input", function() {
                filterBillers();
            });

            function filterBillers() {
                let selectedCategory = $(".bill-type.active").attr("data-category-id");
                let filter = $(".search-biller")?.val()?.toLowerCase();
                let visibleCount = 0;

                $(".biller-item").each(function() {
                    let billerCategory = $(this).attr("data-category-id");
                    let name = $(this).data("biller-name").toLowerCase();
                    let isVisible = !selectedCategory ? name.includes(filter) : (billerCategory ===
                        selectedCategory && name.includes(filter));
                    $(this).toggle(isVisible);
                    if (isVisible) visibleCount++;
                });
                if (visibleCount === 0) {
                    $(".no-results").show();
                } else {
                    $(".no-results").hide();
                }
            }

            $("input[name=amount]").on('input', function() {
                calculation();
            });

            function calculation() {
                let percentCharge = parseFloat($('.percent-charge').val() || 0);
                let fixedCharge = parseFloat($('.fixed-charge').val() || 0);

                if (percentCharge > 0 || fixedCharge > 0) {
                    percentCharge = percentCharge;
                    fixedCharge = fixedCharge;
                } else {
                    percentCharge = parseFloat("{{ $utilityCharge->percent_charge }}") || 0;
                    fixedCharge = parseFloat("{{ $utilityCharge->fixed_charge }}") || 0;
                }

                const amount = parseFloat($('body').find(`input[name="amount"]`).val() || 0);

                const totalPercentCharge = parseFloat(amount / 100 * percentCharge);
                const totalCharge = parseFloat(totalPercentCharge + fixedCharge);
                const totalAmount = parseFloat(amount + totalPercentCharge + fixedCharge);
                $(".processing-fee").text(totalCharge.toFixed(2));
                $(".final-amount").text(totalAmount.toFixed(2));
            }



            $('.breadcrumb-plugins-wrapper').remove();
        })(jQuery);
    </script>
@endpush
