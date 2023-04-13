<div class="form-group">
    <label for="exampleInputEmail1">{{ trans('default.actor_classification_control.classification_name') ?? '分類名稱'}}</label>
    <select id="select-tools" multiple name="groups[]">

    </select>
</div>

<script>
    let select = $('#select-tools').selectize({
        maxItems: null,
        valueField: 'id',
        labelField: 'name',
        searchField: 'name',
        options: {!! \App\Model\TagGroup::all()->toJson() !!},
        create: false
    })

    let control = select[0].selectize;

    control.setValue({{$tag_group_ids}})
</script>