@extends($activeTemplate . 'layouts.app')
@section('app')
    @if(gs('registration'))
    @php
        $registerContent = getContent('register.content', true);
        $policyPages = getContent('policy_pages.element', false, null, true);
    @endphp
    <section class="account">
        <div class="account-inner">
            <div class="account-thumb">
                <img src="{{ getImage('assets/images/frontend/register/' . @$registerContent->data_values->image, '740x600') }}" alt="@lang('image')">
            </div>
            <div class="account-tab">
                <a class="account-logo m-0" href="{{ route('home') }}"><img src="{{ siteLogo() }}" alt="@lang('image')" /></a>
                <div class="account-tab__inner">
                    <ul class="nav custom--tab">
                        <li class="tab__bar"></li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" href="{{ route('user.login') }}">@lang('Login')</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" type="button">@lang('Register')</button>
                        </li>
                    </ul>
                    <div class="account-form">
                        <div class="account-form__content mb-4">
                            <h2 class="account-form__title">{{ __(@$registerContent->data_values->heading) }}</h2>
                            <p class="account-form__desc fs-18">{{ __(@$registerContent->data_values->subheading) }}</p>
                        </div>
                        <form action="{{ route('user.register') }}" method="POST" class="verify-gcaptcha disableSubmission">
                            @csrf
                            <div class="row">
                                @if (session()->get('reference') != null)
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="referenceBy" class="form--label">@lang('Reference by')</label>
                                            <input type="text" name="referBy" id="referenceBy" class="form--control" value="{{ session()->get('reference') }}" readonly>
                                        </div>
                                    </div>
                                @endif

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form--label">@lang('First Name')</label>
                                            <input type="text" class="form-control form--control" name="firstname" value="{{old("firstname")}}" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form--label">@lang('Last Name')</label>
                                            <input type="text" class="form-control form--control" name="lastname" value="{{old("lastname")}}" required>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form--label">@lang('E-Mail Address')</label>
                                            <input type="email" class="form--control checkUser" name="email" value="{{ old('email') }}" required>
                                        </div>
                                    </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form--label">@lang('Password')</label>
                                        <input type="password" class="form--control @if (gs('secure_password')) secure-password @endif" name="password" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form--label">@lang('Confirm Password')</label>
                                        <input type="password" class="form--control" name="password_confirmation" required>
                                    </div>
                                </div>

                                <x-captcha />

                            </div>

                            @if (gs('agree'))
                                <div class="form-group">
                                    <input type="checkbox" id="agree" @checked(old('agree')) name="agree"
                                           required>
                                    <label for="agree">@lang('I agree with')</label> <span>
                                        @foreach ($policyPages as $policy)
                                            <a href="{{ route('policy.pages', slug($policy->data_values->title)) }}"
                                               target="_blank"
                                               class="text--base">{{ __($policy->data_values->title) }}</a>
                                            @if (!$loop->last)
                                                ,
                                            @endif
                                        @endforeach
                                    </span>
                                </div>
                            @endif
                            <div class="form-group">
                                <button type="submit" id="recaptcha" class="btn btn--gradient w-100">@lang('Register')</button>
                            </div>
                            <p class="mb-0">@lang('Already have an account?') <a href="{{ route('user.login') }}">@lang('Login')</a></p>
                        </form>
                        @include($activeTemplate . 'partials.social_login')
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="existModalCenter" role="dialog" aria-labelledby="existModalCenterTitle"
         aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="existModalLongTitle">@lang('You are with us')</h5>
                    <span class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <h6 class="text-center mb-0">@lang('You already have an account please Login ')</h6>
                </div>
                <div class="modal-footer">
                    <a class="btn btn--base btn-sm" href="{{ route('user.login') }}">@lang('Login')</a>
                </div>
            </div>
        </div>
    </div>
    @else
        @include($activeTemplate.'partials.registration_disabled')
    @endif
@endsection
@push('script')
    <script>
        (function($) {
            "use strict";
            $('[name=captcha]').removeClass('form-control').siblings('label').removeClass('form-label').addClass('form--label');
        })(jQuery)
    </script>
@endpush

@push('style')
    <style>
        .form-control:focus {
            box-shadow: none;
        }
    </style>
@endpush

@if (gs('secure_password'))
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif
@push('script')
    <script>
        "use strict";
        (function($) {

            $('.checkUser').on('focusout', function(e) {
                var url = '{{ route('user.checkUser') }}';
                var value = $(this).val();
                var token = '{{ csrf_token() }}';

                var data = {
                    email: value,
                    _token: token
                }

                $.post(url, data, function(response) {
                    if (response.data != false) {
                        $('#existModalCenter').modal('show');
                    }
                });
            });
        })(jQuery);
    </script>
@endpush
