@foreach ($datas as $data)
    <label>
        {!! Form::checkbox('reftext[]', $data->id, '', ['class' => 'checkbox']) !!}
        {{ $data->title }}
    </label>
@endforeach
