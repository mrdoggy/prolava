@extends('layouts.frontend')

@section('main')

<div class="page-header">
   <h1>
      <span class="label label-important label-big">{{ $offer->off }}%</span>
      {{ $offer->title }}
      <small> by
         <a href="{{ route('home.by_company', $offer->company->title) }}">{{ $offer->company->title }}</a>
      </small>
   </h1>
</div>

<div class="pull-left image-container-big">
   <img class="img-rounded" src="" alt="{{ $offer->title }}">
</div>

<div class="description">
   <p>{{ $offer->webDescription() }}</p>
</div>

<div class="clearfix"></div>
<hr>
<p>{{ trans('offers.location') }}:
   <a href="{{ route('home.by_city', $offer->city->name) }}">{{{ $offer->city->name }}}</a>
</p>
<p>{{ trans('offers.tags') }}:
   @foreach($offer->tags as $tag)
   <a class="no_decoration" href="{{ route('home.by_tag', $tag->title) }}">
      <span class="badge">{{{$tag->title}}}</span>
   </a>
   @endforeach
</p>

<hr>

<div class="page-header">
   <h3>{{ trans('offers.user_comments') }} <small>{{ trans('offers.leave_you') }}</small></h3>
</div>

{{ Form::open() }}
<div class="form-group">
{{ Form::textarea('body', Input::old('body'), array('class' => 'input-block-level form-control', 'style' => 'resize: vertical;'))}}
</div>
<div class="form-group">
   {{ Form::select('mark', array(0 => 5, 1 => 4, 2 => 3, 3 => 2, 4 => 1), Input::old('mark', 0), array('class' => 'form-control')) }}
</div>
<div class="form-group">
   {{ Form::submit(trans('offers.comment'), array('class' => 'btn btn-success', 'style' => 'clear: both;')) }}
</div>
{{ Form::close() }}
@include('partials.errors', $errors)
@stop

@if(!$offer->usersComments->count())
<div class="well">{{ trans('offers.you_can_be_first') }}</div>
@endif

@if(Auth::guest() || (!Auth::guest() && !$offer->usersComments->contains(Auth::user()->id)))
{{ Form::open() }}
<div class="form-group">
{{ Form::textarea('body', Input::old('body'), array('class' => 'input-block-level form-control', 'style' => 'resize: vertical;'))}}
</div>
<div class="form-group">
   {{ Form::select('mark', array(5 => 5, 4 => 4, 3 => 3, 2 => 2, 1 => 1), Input::old('mark', 5), array('class' => 'form-control')) }}
</div>
<div class="form-group">
   {{ Form::submit(trans('offers.comment'), array('class' => 'btn btn-success', 'style' => 'clear: both;')) }}
</div>
{{ Form::close() }}
@include('partials.errors', $errors)
@endif

@foreach($offer->usersComments as $user)
<div class="media">
   <a class="pull-left" href="#">
      <img class="media-object" data-src="holder.js/64x64">
   </a>
   <div class="media-body">
      <h4 class="media-heading">{{{ $user->username }}} <span class="label label-success">{{ trans('offers.mark') }}: {{{ $user->pivot->mark }}}</span></h4>
      <p class="muted">{{ str_replace("\r\n", '<br>', e($user->pivot->body)) }}</p>
   </div>
</div>
@endforeach
@stop