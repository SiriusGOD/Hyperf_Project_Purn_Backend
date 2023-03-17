<div class="form-group">
    <label for="exampleInputEmail1">{{ trans('default.tag_control.tag_name') ?? '標籤名稱'}}</label>
    <select id="select-tools" multiple name="tags[]">

    </select>
</div>

<script>
    let select = $('#select-tools').selectize({
        maxItems: null,
        valueField: 'id',
        labelField: 'name',
        searchField: 'name',
        options: {!! \App\Model\Tag::all()->toJson() !!},
        create: false
    })

    let control = select[0].selectize;

    control.setValue({{$tag_ids}})
</script>