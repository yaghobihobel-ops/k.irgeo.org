@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-12">
            <x-admin.ui.card>
                <x-admin.ui.card.body :paddingZero=true>
                    <x-admin.ui.table.layout :renderExportButton="false">
                        <x-admin.ui.table>
                            <x-admin.ui.table.header>
                                <tr>
                                    <th>@lang('Name') | @lang('Calling Codes')</th>
                                    <th>@lang('ISO') | @lang('Continent')</th>
                                    <th>@lang('Currency Name') | @lang('Currency Code')</th>
                                    <th>@lang('Currency Symbol')</th>
                                    <th>@lang('Total Operator')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </x-admin.ui.table.header>
                            <x-admin.ui.table.body>
                                @forelse($countries as $country)
                                    <tr>
                                        <td>
                                            <div>
                                                {{ __($country->name) }} <br />
                                                {{ implode(', ', $country->calling_codes) }}
                                            </div>
                                        </td>
                                        <td>
                                            <div>{{ $country->iso_name }} <br> {{ __($country->continent) }}</div>
                                        </td>
                                        <td>
                                            <div>
                                                {{ __($country->currency_name) }} <br>
                                                {{ $country->currency_code }}
                                            </div>
                                        </td>
                                        <td>{{ $country->currency_symbol }}</td>
                                        <td>
                                            {{ $country->operators_count }}
                                        </td>
                                        <td>
                                            <x-admin.other.status_switch :status="$country->status" :action="route('admin.airtime.country.status', $country->id)"
                                                title="Country" />
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.airtime.operators', $country->iso_name) }}"
                                                class = "btn  btn-outline--primary">
                                                <i class="las la-list me-1"></i>@lang('Operators')
                                            </a>
                                        </td>

                                    </tr>
                                @empty
                                    <x-admin.ui.table.empty_message />
                                @endforelse
                            </x-admin.ui.table.body>
                        </x-admin.ui.table>
                        @if ($countries->hasPages())
                            <x-admin.ui.table.footer>
                                {{ paginateLinks($countries) }}
                            </x-admin.ui.table.footer>
                        @endif
                    </x-admin.ui.table.layout>
                </x-admin.ui.card.body>
            </x-admin.ui.card>
        </div>
    </div>
    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-admin.ui.btn.add text="Fetch More Country" :href="route('admin.airtime.fetch.countries')" />
@endpush
