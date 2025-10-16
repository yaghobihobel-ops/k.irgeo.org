<x-ovo-form identifier="id" identifierValue="{{ @$company->company->form_id }}" :filledData="$company->user_data" />
<input type="hidden" name="company_id" value="{{ @$company->company->id }}">