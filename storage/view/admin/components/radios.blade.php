@foreach ($datas as $row)
    <label style="margin-right:60px;">
        {!! Form::radio("{$name}", $row->id, $row->id == $checked, ['class' => $name]) !!}
        {{ $row->title }}
    </label>
@endforeach
