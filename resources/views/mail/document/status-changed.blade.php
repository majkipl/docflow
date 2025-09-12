<x-mail::message>
    # Zmiana statusu dokumentu

    Dokument **{{ $title }}** zmienił swój status z `{{ $oldState }}` na `{{ $newState }}`.

    <x-mail::button :url="url('/documents/' . $document->id)">
        Zobacz dokument
    </x-mail::button>

    Dziękujemy,<br>
    System DocFlow
</x-mail::message>
