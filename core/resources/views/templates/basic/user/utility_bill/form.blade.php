@if ($hideFile == 'yes')
    <x-ovo-form identifier="id" :identifierValue="$form->id" :noFileType="true" />
@else
    <x-ovo-form identifier="id" :identifierValue="$form->id" :noFileType="false" />
@endif
