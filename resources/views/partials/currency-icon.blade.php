{{--
  Usage: {!! currencyIconHtml('AED') !!}
  or: @include('partials.currency-icon', ['code' => 'AED'])
--}}
{!! currencyIconHtml($code ?? null, trim('currency-icon '.($class ?? ''))) !!}
