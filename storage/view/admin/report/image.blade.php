<img src="{{env('IMAGE_GROUP_DECRYPT_URL') . $model['url']}}">
<br>
@foreach($model['images'] as $key => $row)
    <img src="{{env('IMAGE_GROUP_DECRYPT_URL') . $model['url']}}">
    @if(($key+1) % 3 == 0)
        <br>
    @endif
@endforeach