@extends('layouts.master')

@section('title')
    @lang('common.edit')
@stop

@component('gzero-base::account.menu')@endcomponent

@component('sections.content', ['class' => 'col-sm-8'])
    <h1 class="page-header">@lang('common.edit')</h1>
    @if(!$isUserEmailSet)
        <div class="alert alert-info">
            <i class="fa fa-info-circle"><!-- icon --></i> @lang('common.set_email_to_edit_account')
        </div>
    @endif
    <div class="row">
        <div class="col-md-6">
            <form id="edit-account-form" action="#" method="POST" role="form">
                @if($isUserEmailSet)
                    <div class="form-group">
                        <label class="control-label" for="name">@lang('common.nick')</label>
                        <input type="text" id="name" name="name" value="{{ $user->name }}"
                               class="form-control{{ $errors->first('name') ? ' is-invalid' : '' }}"
                               placeholder="@lang('common.name')">
                        @if($errors->first('name'))
                            <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                        @endif
                    </div>
                @else
                    <div class="form-group">
                        <label class="control-label">@lang('common.name')</label>
                        <p class="form-control-static">{{ $user->name }}</p>
                        <input type="hidden" name="name" value="{{ $user->name }}">
                    </div>
                @endif
                <div class="form-group">
                    <label class="control-label" for="email">@choice('common.email', 1)</label>
                    <input type="email" id="email" name="email" value="{{ $user->email }}"
                           class="form-control{{ $errors->first('email') ? ' is-invalid' : '' }}"
                           placeholder="@choice('common.email', 1)">
                    @if($errors->first('email'))
                        <div class="invalid-feedback">{{ $errors->first('email') }}</div>
                    @endif
                </div>
                @if($isUserEmailSet)
                    <div class="form-group">
                        <label class="control-label" for="firstName">@lang('common.first_name')</label>
                        <input type="text" id="firstName" name="firstName" value="{{ $user->firstName }}"
                               class="form-control{{ $errors->first('firstName') ? ' is-invalid' : '' }}"
                               placeholder="@lang('common.first_name')">
                        @if($errors->first('firstName'))
                            <div class="invalid-feedback">{{ $errors->first('firstName') }}</div>
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="lastName">@lang('common.last_name')</label>
                        <input type="text" id="lastName" name="lastName" value="{{ $user->lastName }}"
                               class="form-control{{ $errors->first('lastName') ? ' is-invalid' : '' }}"
                               placeholder="@lang('common.last_name')">
                        @if($errors->first('lastName'))
                            <div class="invalid-feedback">{{ $errors->first('lastName') }}</div>
                        @endif
                    </div>
                @else
                    <div class="form-group">
                        <label class="control-label">@lang('common.first_name')</label>
                        <p class="form-control-static">{{ $user->firstName }}</p>
                    </div>
                    <div class="form-group">
                        <label class="control-label">@lang('common.last_name')</label>
                        <p class="form-control-static">{{ $user->lastName }}</p>
                    </div>
                @endif
                @if($isUserEmailSet)
                    @if($user->password)
                        <div class="separator">
                            <span>@lang('common.password_change')</span>
                        </div>
                        <p class="text-muted">
                            <i class="fa fa-info-circle"><!-- icon --></i> @lang('common.leave_blank')
                        </p>
                    @else
                        <div class="separator">
                            <span>@lang('common.password_set')</span>
                        </div>
                        <p class="text-success">
                            <i class="fa fa-info-circle"><!-- icon --></i> @lang('common.set_password_to_login')
                        </p>
                    @endif
                    <div class="form-group">
                        <label class="control-label" for="password">@lang('common.new_password')</label>
                        <input type="password" id="password" name="password"
                               class="form-control{{ $errors->first('password') ? ' is-invalid' : '' }}"
                               placeholder="@lang('common.new_password')">
                        @if($errors->first('password'))
                            <p class="invalid-feedback">{{ $errors->first('password') }}</p>
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="passwordConfirmation">@lang('common.password_repeat')</label>
                        <input type="password" id="passwordConfirmation" name="password_confirmation"
                               class="form-control{{ $errors->first('password_confirmation') ? ' is-invalid' : '' }}"
                               placeholder="@lang('common.password_repeat')">
                        @if($errors->first('password_confirmation'))
                            <div class="invalid-feedback">{{ $errors->first('password_confirmation') }}</div>
                        @endif
                    </div>
                @endif
                <button id="edit-account" type="submit" class="btn btn-primary">@lang('common.save')</button>
            </form>
        </div>
    </div>
@endcomponent
@push('footer')
    <script type="text/javascript">
        $(function() {
            $('#edit-account').click(function(event) {
                event.preventDefault();
                Loading.start('#main-container');
                $.ajax({
                    url: "{{ request()->getScheme() }}://api.{{ request()->getHTTPHost() }}/v1/user/account",
                    headers: {'X-CSRF-TOKEN': Laravel.csrfToken},
                    xhrFields: {
                        withCredentials: true
                    },
                    data: $('#edit-account-form').serializeObject(),
                    type: 'PUT',
                    success: function(xhr) {
                        @if($isUserEmailSet)
                             Loading.stop();
                        // set success message
                        setGlobalMessage('success', "@lang('common.changes_saved_message')");
                        hideMessages();
                        clearFormValidationErrors();
                        @else
                            location.reload();
                        @endif
                    },
                    error: function(xhr) {
                        Loading.stop();
                        if (typeof xhr.responseJSON !== 'undefined' && xhr.status === 422) {
                            // clear previous errors
                            clearFormValidationErrors();
                            $.each(xhr.responseJSON.error.errors, function(index, error) {
                                // set form errors
                                setFormValidationErrors(index, error);
                            });
                        }
                    }
                });
            })
        });
    </script>
@endpush
