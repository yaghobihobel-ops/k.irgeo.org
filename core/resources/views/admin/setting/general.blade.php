@extends('admin.layouts.app')
@section('panel')
    @php
        $pusherConfig = gs('pusher_config');
    @endphp
    <form method="POST" enctype="multipart/form-data">
        @csrf
        <x-admin.ui.card>
            <x-admin.ui.card.body>
                <div class="row">
                    <div class="col-xxl-4 col-xl-6 col-sm-6">
                        <div class="form-group">
                            <label> @lang('Site Title')</label>
                            <input class="form-control" type="text" name="site_name" required value="{{ gs('site_name') }}">
                        </div>
                    </div>
                    <div class="form-group col-xxl-4 col-xl-6 col-sm-6">
                        <label class="required"> @lang('Timezone')</label>
                        <select class="form-control select2" name="timezone">
                            @foreach ($timezones as $key => $timezone)
                                <option value="{{ @$key }}" @selected(@$key == $currentTimezone)>
                                    {{ __($timezone) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-xxl-4 col-xl-6 col-sm-6">
                        <label class="required">
                            @lang('User PIN Digits')
                        </label>
                        <span title="@lang('Specify the required number of PIN digits for user registration and login.')">
                            <i class="las la-info-circle"></i>
                        </span>
                        <div class="input-group input--group">
                            <input type="text" class="form-control" name="user_pin_digits"
                                value="{{ gs('user_pin_digits') }}">
                            <span class="input-group-text">
                                @lang('Digits')
                            </span>
                        </div>
                    </div>
                    <div class="form-group col-xxl-4 col-xl-6 col-sm-6">
                        <label class="required"> @lang('Date Format')</label>
                        <select class="form-control select2" name="date_format" data-minimum-results-for-search="-1">
                            @foreach (supportedDateFormats() as $dateFormat)
                                <option value="{{ @$dateFormat }}" @selected(gs('date_format') == $dateFormat)>
                                    {{ $dateFormat }} ({{ date($dateFormat) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-xxl-4 col-xl-6 col-sm-6">
                        <label class="required"> @lang('Time Format')</label>
                        <select class="form-control select2" name="time_format" data-minimum-results-for-search="-1">
                            @foreach (supportedTimeFormats() as $key => $timeFormat)
                                <option value="{{ @$timeFormat }}" @selected(gs('time_format') == $timeFormat)>
                                    {{ __($timeFormat) }} ({{ date($timeFormat) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-xxl-4 col-xl-6 col-sm-6">
                        <label class="required"> @lang('OTP Expiration')</label>
                        <div class="input-group input--group">
                            <input type="text" class="form-control" name="otp_expiration"
                                value="{{ getAmount(gs('otp_expiration')) }}">
                            <span class="input-group-text">
                                @lang('Seconds')
                            </span>
                        </div>
                    </div>

                    <div class="form-group col-xxl-4 col-xl-6 col-sm-6">
                        <label class="required"> @lang('Site Primary Color')</label>
                        <div class="input-group color-input">
                            <input type="text" class="form-control colorCode" name="base_color"
                                value="{{ gs('base_color') }}">
                            <span class="input-group-text">
                                <input type='text' class="form-control colorPicker" value="{{ gs('base_color') }}">
                            </span>
                        </div>
                    </div>
                    <div class="form-group col-xxl-4 col-xl-6 col-sm-6">
                        <label class="required"> @lang('Site Secondary Color')</label>
                        <div class="input-group color-input">
                            <input type="text" class="form-control colorCode" name="secondary_color"
                                value="{{ gs('secondary_color') }}">
                            <span class="input-group-text">
                                <input type='text' class="form-control colorPicker" value="{{ gs('secondary_color') }}">
                            </span>
                        </div>
                    </div>
                    <div class="form-group col-xxl-4 col-xl-6 col-sm-6">
                        <label class="required"> @lang('Agent Panel Base Color')</label>
                        <div class="input-group color-input">
                            <input type="text" class="form-control colorCode" name="agent_panel_color"
                                value="{{ gs('agent_panel_color') }}">
                            <span class="input-group-text">
                                <input type='text' class="form-control colorPicker"
                                    value="{{ gs('agent_panel_color') }}">
                            </span>
                        </div>
                    </div>
                    <div class="form-group col-xxl-4 col-xl-6 col-sm-6">
                        <label class="required"> @lang('Merchant Panel Base Color')</label>
                        <div class="input-group color-input">
                            <input type="text" class="form-control colorCode" name="merchant_panel_color"
                                value="{{ gs('merchant_panel_color') }}">
                            <span class="input-group-text">
                                <input type='text' class="form-control colorPicker"
                                    value="{{ gs('merchant_panel_color') }}">
                            </span>
                        </div>
                    </div>
                    <div class="col-xxl-4 col-xl-6 col-sm-6">
                        <div class="form-group">
                            <label>@lang('Currency')</label>
                            <input class="form-control" type="text" name="cur_text" required
                                value="{{ gs('cur_text') }}">
                        </div>
                    </div>
                    <div class="col-xxl-4 col-xl-6 col-sm-6">
                        <div class="form-group">
                            <label>@lang('Currency Symbol')</label>
                            <input class="form-control" type="text" name="cur_sym" required value="{{ gs('cur_sym') }}">
                        </div>
                    </div>
                    <div class="form-group col-xxl-4 col-xl-6 col-sm-6 ">
                        <label class="required"> @lang('Currency Showing Format')</label>
                        <select class="select2 form-control" name="currency_format" data-minimum-results-for-search="-1">
                            <option value="1" @selected(gs('currency_format') == Status::CUR_BOTH)>
                                @lang('Show Currency Text and Symbol Both')({{ gs('cur_sym') }}{{ showAmount(100, currencyFormat: false) }}
                                {{ __(gs('cur_text')) }})
                            </option>
                            <option value="2" @selected(gs('currency_format') == Status::CUR_TEXT)>
                                @lang('Show Currency Text Only')({{ showAmount(100, currencyFormat: false) }} {{ __(gs('cur_text')) }})
                            </option>
                            <option value="3" @selected(gs('currency_format') == Status::CUR_SYM)>
                                @lang('Show Currency Symbol Only')({{ gs('cur_sym') }}{{ showAmount(100, currencyFormat: false) }})
                            </option>
                        </select>
                    </div>

                    <div class="form-group col-xxl-4 col-xl-6 col-sm-6 ">
                        <label class="required"> @lang('Allow Precision')</label>
                        <select class="select2 form-control" name="allow_precision" data-minimum-results-for-search="-1">
                            @foreach (range(1, 8) as $digit)
                                <option value="{{ $digit }}" @selected(gs('allow_precision') == $digit)>
                                    {{ $digit }}
                                    @lang('Digit')({{ showAmount(100, currencyFormat: false, decimal: $digit) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-xxl-4 col-xl-6 col-sm-6 ">
                        <label class="required"> @lang('Thousand Separator')</label>
                        <select class="select2 form-control" name="thousand_separator"
                            data-minimum-results-for-search="-1">
                            @foreach (supportedThousandSeparator() as $k => $supportedThousandSeparator)
                                <option value="{{ $k }}" @selected(gs('thousand_separator') == $k)>
                                    {{ __($supportedThousandSeparator) }}
                                    @if ($k == 'space')
                                        ({{ showAmount(1000, currencyFormat: false, separator: ' ') }})
                                    @elseif($k == 'none')
                                        (@lang('10000'))
                                    @else
                                        ({{ showAmount(1000, currencyFormat: false, separator: $k) }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-xxl-4 col-xl-6 col-sm-6">
                        <label> @lang('Record to Display Per Page')</label>
                        <select class="select2 form-control" name="paginate_number" data-minimum-results-for-search="-1">
                            <option value="20" @selected(gs('paginate_number') == 20)>@lang('20 items')</option>
                            <option value="50" @selected(gs('paginate_number') == 50)>@lang('50 items')</option>
                            <option value="100" @selected(gs('paginate_number') == 100)>@lang('100 items')</option>
                        </select>
                    </div>
                    <div class="form-group col-xxl-4 col-xl-6 col-sm-6">
                        <label> @lang('Supported OTP Type')</label>
                        <select class="select2 form-control" name="supported_otp_type[]"
                            data-minimum-results-for-search="-1" multiple>
                            @foreach (getAvailableOtpVerificationType() ?? [] as $type)
                                <option value="{{ $type }}" @selected(in_array($type, gs('supported_otp_type') ?? []))>
                                    {{ ucfirst(__($type)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-xxl-4 col-xl-6 col-sm-6">
                        <label> @lang('Recommended Transactional Amount')</label>
                        <select name="quick_amounts[]" class="form-control select2-auto-tokenize" multiple="multiple"
                            required>
                            @foreach (gs('quick_amounts') ?? [] as $amount)
                                <option value="{{ $amount }}" selected>{{ __($amount) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-xxl-4 col-xl-6 col-sm-6">
                        <div class="form-group">
                            <div class="d-flex align-items-center justify-content-between">
                                <label>@lang('Preloader Image')</label>
                                <img class="preloader_image"
                                    src="{{ getImage(getFilePath('preloader') . '/' . gs('preloader_image')) }}"
                                    alt="image">
                            </div>
                            <input class="form-control" name="preloader_image" type="file">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="my-4">
                            <h5 class="divider-title">
                                @lang('PUSHER CONFIGURATION')
                            </h5>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>@lang('Pusher App ID') </label>
                            <input type="text" class="form-control" placeholder="@lang('App ID')"
                                name="pusher_app_id" value="{{ @$pusherConfig->app_id }}" >
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>@lang('Pusher App Key') </label>
                            <input type="text" class="form-control" placeholder="@lang('App Key')"
                                name="pusher_app_key" value="{{ @$pusherConfig->app_key }}" >
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>@lang('Pusher App Secret') </label>
                            <input type="text" class="form-control" placeholder="@lang('App Secret')"
                                name="pusher_app_secret" value="{{ @$pusherConfig->app_secret }}" >
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>@lang('Pusher Cluster') </label>
                            <input type="text" class="form-control" placeholder="@lang('Cluster')"
                                name="pusher_cluster" value="{{ @$pusherConfig->cluster }}" >
                        </div>
                    </div>
                    <div class="col-12">
                        <x-admin.ui.btn.submit />
                    </div>
                </div>
            </x-admin.ui.card.body>
        </x-admin.ui.card>
    </form>
@endsection

@push('script-lib')
    <script src="{{ asset('assets/admin/js/spectrum.js') }}"></script>
@endpush

@push('style-lib')
    <link rel = "stylesheet" href = "{{ asset('assets/admin/css/spectrum.css') }}">
@endpush

@push('script')
    <script>
        "use strict";
        (function($) {

            $('input[name="preloader_image"]').on('change', function(e) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('.preloader_image').attr('src', e.target.result);
                };
                reader.readAsDataURL(this.files[0]);
            });

            $('.colorPicker').spectrum({
                color: $(this).data('color'),
                change: function(color) {
                    changeColor($(this), color.toHexString())
                }
            });

            $('.colorCode').on('input', function() {
                var clr = $(this).val();
                $(this).closest('.form-group').find('.colorPicker').spectrum({
                    color: clr,
                    change: function(color) {
                        changeColor($(this), color.toHexString());
                    }
                });
                changeColor($(this), `#${clr}`)
            });

            $.each($('.colorCode'), function(i, element) {
                const $element = $(element);
                const colorCode = `#${$element.val()}`;
                changeColor($element, colorCode);
            });

            function changeColor($this, colorCode) {
                const $parent = $this.closest('.form-group');
                $parent.find('.input-group-text').css('border-color', colorCode);
                $parent.find('.sp-replacer').css('background', colorCode);
                $parent.find('.colorCode').val(colorCode.replace(/^#?/, ''));
            }
        })(jQuery);
    </script>
@endpush
@push('style')
    <style>
        [data-theme=dark] .sp-picker-container {
            border-left: solid 1px hsl(var(--light));
            background: hsl(var(--light));
        }

        [data-theme=dark] .sp-container {
            border-color: hsl(var(--border-color)) !important;
            border: solid 1px hsl(var(--border-color));
        }

        .preloader_image {
            width: 40px;
            height: 40px;
        }

        .divider-title {
            position: relative;
            text-align: center;
            width: max-content;
            margin: 0 auto;
        }

        .divider-title::before {
            position: absolute;
            content: '';
            top: 14px;
            left: -90px;
            background: #6b6b6b65;
            height: 2px;
            width: 80px;
        }

        .divider-title::after {
            position: absolute;
            content: '';
            top: 14px;
            right: -90px;
            background: #6b6b6b65;
            height: 2px;
            width: 80px;
        }

        label.required.no-required:after {
            display: none !important;
        }
    </style>
@endpush
