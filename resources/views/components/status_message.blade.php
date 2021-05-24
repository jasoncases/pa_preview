<!--  -->
@if ($statusBox->status == 1)
<input id="smBox" class="smBoxCheckbox" type="checkbox" />
<div class="smBox">
    <label for="smBox" class="smBoxLabel"><i class="far fa-times"></i></label>
    <ul>{{$statusBox->message}}</ul>
</div>
@endif