@php
    $type = isset($register) ? 'Register' : 'Login';
@endphp
@if (@gs('socialite_credentials')->linkedin->status || @gs('socialite_credentials')->facebook->status == Status::ENABLE || @gs('socialite_credentials')->google->status == Status::ENABLE)
    <div class="auth-devide">
        <span>OR</span>
    </div>
    <div class="auth--btn-wrapper">
        @if (@gs('socialite_credentials')->google->status == Status::ENABLE)
            <div class="auth--btn-inner">
                <a class="btn auth--btn w-100" href="{{ route('user.social.login', 'google') }}">
                    <span class="icon">
                        <img src="{{ asset($activeTemplateTrue . 'images/google.svg') }}" alt="">
                    </span>
                    @lang('Google')
                </a>
            </div>
        @endif
            @if (@gs('socialite_credentials')->facebook->status == Status::ENABLE)
            <div class="auth--btn-inner">
                <a class="btn auth--btn w-100" href="{{ route('user.social.login', 'facebook') }}">
                    <span class="icon">
                        <img src="{{ asset($activeTemplateTrue . 'images/facebook.svg') }}" alt="">
                    </span>
                    @lang('Facebook')
                </a>
            </div>
        @endif
            @if (@gs('socialite_credentials')->linkedin->status == Status::ENABLE)
            <div class="auth--btn-inner">
                <a class="btn auth--btn w-100" href="{{ route('user.social.login', 'linkedin') }}">
                    <span class="icon">
                        <img src="{{ asset($activeTemplateTrue . 'images/linkdin.svg') }}" alt="">
                    </span>
                    @lang('Linkedin')
                </a>
            </div>
        @endif
    </div>
@endif
