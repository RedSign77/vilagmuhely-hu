<x-mail::message>
{!! \Illuminate\Mail\Markdown::parse($body) !!}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
