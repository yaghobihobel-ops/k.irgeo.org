@extends($activeTemplate . 'layouts.agent')
@section('content')
    <div class="card custom--card">
        <div class="card-header">
            <form class="table-search no-submit-loader">
                <input type="search" name="search" class="form-control form--control" value="{{ request()->search }}"
                    placeholder="@lang('Search by transactions')">
                <button class="icon px-3" type="submit">
                    <i class="fa fa-search"></i>
                </button>
            </form>
        </div>
        <div class="card-body p-0">
            <table class="table table--responsive--lg">
                <thead>
                    <tr>
                        <th>@lang('Gateway | Transaction')</th>
                        <th>@lang('Initiated')</th>
                        <th>@lang('Amount')</th>
                        <th>@lang('Conversion')</th>
                        <th>@lang('Status')</th>
                        <th>@lang('Details')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deposits as $deposit)
                        <tr>
                            <td>
                                <div class="text-start">
                                    <span class="fw-bold">
                                        <span class="text--base">
                                            @if ($deposit->method_code < 5000)
                                                {{ __(@$deposit->gateway->name) }}
                                            @else
                                                @lang('Google Pay')
                                            @endif
                                        </span>
                                    </span>
                                    <br>
                                    <small> {{ $deposit->trx }} </small>
                                </div>
                            </td>
                            <td>
                                <div class="text-end text-xl-center">
                                    {{ showDateTime($deposit->created_at) }}<br>{{ diffForHumans($deposit->created_at) }}
                                </div>
                            </td>
                            <td>
                                <div class="text-end text-xl-center">
                                    {{ showAmount($deposit->amount) }} + <span class="text--danger" data-bs-toggle="tooltip"
                                        title="@lang('Processing Charge')">{{ showAmount($deposit->charge) }}
                                    </span>
                                    <br>
                                    <strong data-bs-toggle="tooltip" title="@lang('Amount with charge')">
                                        {{ showAmount($deposit->amount + $deposit->charge) }}
                                    </strong>
                                </div>
                            </td>
                            <td>
                                <div class="text-end text-xl-center">

                                    {{ showAmount(1) }} =
                                    {{ showAmount($deposit->rate, currencyFormat: false) }}
                                    {{ __($deposit->method_currency) }}
                                    <br>
                                    <strong>{{ showAmount($deposit->final_amount, currencyFormat: false) }}
                                        {{ __($deposit->method_currency) }}</strong>
                                </div>
                            </td>
                            <td>
                                <div class="text-end text-xl-center">
                                    @php echo $deposit->statusBadge @endphp
                                </div>
                            </td>
                            @php
                                $details = [];
                                if ($deposit->method_code >= 1000 && $deposit->method_code <= 5000) {
                                    foreach (@$deposit->detail ?? [] as $key => $info) {
                                        $details[] = $info;
                                        if ($info->type == 'file') {
                                            $details[$key]->value = route(
                                                'agent.download.attachment',
                                                encrypt(getFilePath('verify') . '/' . $info->value),
                                            );
                                        }
                                    }
                                }
                            @endphp
                            <td>
                                @if ($deposit->method_code >= 1000 && $deposit->method_code <= 5000)
                                    <button type="button" class="btn btn--light btn--sm  detailBtn"
                                        data-info="{{ json_encode($details) }}"
                                        @if ($deposit->status == Status::PAYMENT_REJECT) data-admin_feedback="{{ $deposit->admin_feedback }}" @endif>
                                        <i class="fas fa-desktop"></i> @lang('Details')
                                    </button>
                                @else
                                    <button type="button" class="btn btn--light btn--sm " data-bs-toggle="tooltip"
                                        title="@lang('Automatically processed')">
                                        <i class="fas fa-check"></i> @lang('Completed')
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        @include('Template::partials.empty_message')
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>



    <div id="detailModal" class="modal fade custom--modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Details')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="list-group userData mb-2 list-group-flush">
                    </ul>
                    <div class="feedback"></div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('script')
    <script>
        (function($) {
            "use strict";
            $('.detailBtn').on('click', function() {
                var modal = $('#detailModal');

                var userData = $(this).data('info');
                var html = '';
                if (userData) {
                    userData.forEach(element => {
                        if (element.type != 'file') {
                            html += `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>${element.name}</span>
                                <span">${element.value}</span>
                            </li>`;
                        } else {
                            html += `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>${element.name}</span>
                                <span"><a href="${element.value}"><i class="fa-regular fa-file"></i> @lang('Attachment')</a></span>
                            </li>`;
                        }
                    });
                }

                modal.find('.userData').html(html);

                if ($(this).data('admin_feedback') != undefined) {
                    var adminFeedback = `
                        <div class="my-3">
                            <strong>@lang('Admin Feedback')</strong>
                            <p>${$(this).data('admin_feedback')}</p>
                        </div>
                    `;
                } else {
                    var adminFeedback = '';
                }

                modal.find('.feedback').html(adminFeedback);


                modal.modal('show');
            });

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title], [data-title], [data-bs-title]'))
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

        })(jQuery);
    </script>
@endpush


@push('breadcrumb-plugins')
    <a href="{{ route('agent.add.money.create') }}" class="btn btn--base btn--md">
        <span class="icon"><i class="fa fa-plus-circle"></i></span>
        @lang('Add Money')
    </a>
@endpush
