@extends('admin.layouts.app')
@section('panel')
    <div class="row gy-4">
        <div class="col-12">
            <x-admin.ui.card>
                <x-admin.ui.card.body>
                    <p class="text--primary">@lang('Important: Reloadly Account Configuration Required!')</p>
                    <p class="text--warning">@lang('Please ensure that the currency of your Reloadly account matches the currency configured on your site. Maintaining consistency between currencies is essential to avoid potential issues during user top-ups and to ensure seamless transaction processing.')</p>
                </x-admin.ui.card.body>
            </x-admin.ui.card>
        </div>
        <div class="col-md-12">
            <x-admin.ui.card>
                <x-admin.ui.card.body>
                    <form action="{{ route('admin.api.config.reloadly.save') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Client ID')</label>
                                    <input class="form-control" name="credentials[client_id]" type="text"
                                        value="{{ $apiConfig->credentials->client_id }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Client Secret')</label>
                                    <input class="form-control" name="credentials[client_secret]" type="text"
                                        value="{{ $apiConfig->credentials->client_secret }}" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" name="test_mode" id="testMode"
                                            @checked($apiConfig->test_mode)>
                                        <label class="form-check-label" for="testMode">
                                            @lang('Test Mode')
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <x-admin.ui.btn.submit />
                    </form>
                </x-admin.ui.card.body>
            </x-admin.ui.card>
        </div>
    </div>

    <div class="modal" id="helpModal" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Need Help')?</h5>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p>@lang('For using airtime top-up you need to follow the bellow steps'):</p>
                    <div class="instruction-wrapper">
                        <p class="instruction">
                            @lang('If you haven\'t registered for a Reloadly account yet, begin by creating one. Visit the registration page') <a target="_blank"
                                href="https://www.reloadly.com/registration">@lang('here')</a> @lang('to sign up.')
                        </p>
                        <p class="instruction">
                            @lang('Once registration is complete, sign in to your Reloadly account. Navigate to the developers menu, where you\'ll find your API client ID and API client secret. Copy these credentials.')
                        </p>
                        <p class="instruction">
                            @lang('Fill out the form with the copied API client ID and API client secret.')
                        </p>
                        <p class="instruction">
                            @lang('Ensure that your Reloadly account has sufficient funds to support airtime top-up. Refer to their documentation') <a target="_blank"
                                href="https://developers.reloadly.com/airtime/introduction">@lang('here')</a>
                            @lang(' for more information.')
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <button type="button" class="btn btn-sm btn-outline--dark" data-bs-toggle="modal" data-bs-target="#helpModal"><i
            class="la la-question"></i>@lang('Help')</button>
@endpush

@push('style')
    <style>
        .instruction-wrapper {
            display: flex;
            flex-direction: column;
            gap: 10px;
            counter-reset: count;
        }

        .instruction::before {
            counter-increment: count;
            content: counters(count, ".") ".";
        }
    </style>
@endpush
