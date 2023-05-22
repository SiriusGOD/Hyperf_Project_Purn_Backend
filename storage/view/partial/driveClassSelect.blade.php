<div class="form-group">
    <label for="exampleInputEmail1">{{ trans('default.drive_class_control.drive_class_name') ?? '車群類別名稱'}}</label>
    <select id="select-tools" multiple name="groups[]">

    </select>
</div>

<script>
    let select = $('#select-tools').selectize({
        maxItems: null,
        valueField: 'id',
        labelField: 'name',
        searchField: 'name',
        options: {!! \App\Model\DriveClass::whereNull('deleted_at')->get()->toJson() !!},
        create: false
    })

    let control = select[0].selectize;

    control.setValue({{$drive_class_ids}})
</script>