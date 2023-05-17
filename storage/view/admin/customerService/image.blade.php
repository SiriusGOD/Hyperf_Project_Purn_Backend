@foreach($model as $key => $row)
    <img src="{{env('IMAGE_GROUP_DECRYPT_URL') . $row['url']}}">
    @if(($key+1) % 3 == 0)
        <br>
    @endif
@endforeach