<div class="form-group">
    <label for="exampleInputEmail1">{{ trans('default.pay_control.pay_method') ?? '支付方式'}}</label>
    <select id="select-tools" multiple name="pay_groups[]">

    </select>
</div>

<script>
    let select = $('#select-tools').selectize({
        maxItems: null,
        valueField: 'id',
        labelField: 'name',
        searchField: 'name',
        options: {!! \App\Model\Pay::where('expire',\App\Model\Pay::EXPIRE['no'])->get()->toJson() !!},
        create: false
    })

    let control = select[0].selectize;

    control.setValue({{$pay_ids}})
</script>