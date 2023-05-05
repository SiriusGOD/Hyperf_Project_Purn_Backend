@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <div class="col-sm-12 col-md-12 mb-1">
                                <form action="/admin/product/choose" method="get">
                                    <label for="exampleInputEmail1">{{trans('default.product_control.product_choose_type') ?? '選擇商品類型'}}</label>
                                    <select  class="form-control-sm" name="product_type" >
                                        <option value="">選擇商品類型</option>
                                        <!-- <@foreach(\App\Model\Product::TYPE_LIST as $type)
                                        <option value="{{$type}}" {{$product_type == $type ? 'selected' : ''}}>
                                            {{\App\Model\Product::TYPE_LIST_NAME[$type]}}
                                        </option>
                                        @endforeach -->
                                        <option value="{{\App\Model\Product::TYPE_LIST[2]}}" {{$product_type == \App\Model\Product::TYPE_LIST[2] ? 'selected' : ''}}>
                                            {{\App\Model\Product::TYPE_LIST_NAME['member']}}
                                        </option>
                                        <option value="{{\App\Model\Product::TYPE_LIST[3]}}" {{$product_type == \App\Model\Product::TYPE_LIST[3] ? 'selected' : ''}}>
                                            {{\App\Model\Product::TYPE_LIST_NAME['points']}}
                                        </option>
                                    </select>
                                    <label for="exampleInputEmail1">{{trans('default.product_control.product_name_search') ?? '查詢名稱'}}: </label>
                                    <input type="text" name="product_name" id="product_name" value="" placeholder="請輸入影片或圖片名稱">
                                    
                                    <button type="submit" class="btn btn-primary">查詢</button>
                                </form>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <table id="example2" class="table table-bordered table-hover dataTable dtr-inline"
                                       aria-describedby="example2_info">
                                    <thead>
                                    <tr>
                                    <th class="sorting sorting_asc" tabindex="0" aria-controls="example2"
                                            rowspan="1"
                                            colspan="1" aria-sort="ascending"
                                            aria-label="Rendering engine: activate to sort column descending">{{trans('default.id') ?? '序號'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Engine version: activate to sort column ascending">
                                            {{trans('default.type') ?? '類型'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.name') ?? '名稱'}}
                                        </th>
                                        <!-- <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Engine version: activate to sort column ascending">
                                            {{trans('default.image') ?? '圖片'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Engine version: activate to sort column ascending">
                                            {{trans('default.preview') ?? '預覽'}}
                                        </th> -->
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="CSS grade: activate to sort column ascending">{{trans('default.action') ?? '動作'}}
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(!empty($datas))
                                    @foreach($datas as $model)
                                        <tr class="odd">
                                            <td class="sorting_1 dtr-control">{{ $model->id}}</td>
                                            <td>
                                            {{trans('default.product_control.product_type_name')[$product_type]}}
                                            </td>
                                            <td>{{ $model->title}}</td>
                                            <!-- <td>{{ $model->img_thumb}}</td>
                                            <td>{{ $model->m3u8}}</td> -->
                                            <td>
                                                @if(authPermission('product-create'))
                                                    <div class="row mb-1">
                                                    <a href="/admin/product/create?id={{$model->id}}&product_type={{$product_type}}" class="btn btn-primary">{{trans('default.create') ?? '新增'}}</a>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    @endif
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <th rowspan="1" colspan="1">{{trans('default.id') ?? '序號'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.type') ?? '類型'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.name') ?? '名稱'}}</th>
                                        <!-- <th rowspan="1" colspan="1">{{trans('default.image') ?? '圖片'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.preview') ?? '預覽'}}</th> -->
                                        <th rowspan="1" colspan="1">{{trans('default.action') ?? '動作'}}</th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="example2_info" role="status" aria-live="polite">
                                {{trans('default.table_page_info',[
                                        'page' => $page,
                                        'total' => $total,
                                        'last_page' => $last_page,
                                        'step' => $step,
                                    ]) ?? '顯示第 $page 頁
                                    共 $total 筆
                                    共 $last_page 頁
                                    每頁顯示 $step 筆'}}
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">
                                    <ul class="pagination">
                                        <li class="paginate_button page-item previous {{$page <= 1 ? 'disabled' : ''}}" id="example2_previous">
                                            <a href="{{$prev}}"
                                               aria-controls="example2" data-dt-idx="0" tabindex="0"
                                               class="page-link">{{trans('default.pre_page') ?? '上一頁'}}</a>
                                        </li>
                                        <li class="paginate_button page-item next {{$last_page <= $page ? 'disabled' : ''}}" id="example2_next">
                                            <a href="{{$next}}"
                                               aria-controls="example2"
                                               data-dt-idx="7"
                                               tabindex="0"
                                               class="page-link">{{trans('default.next_page') ?? '下一頁'}}</a>
                                        </li>
                                    </ul>



                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->


        </div>
        <!-- /.col -->
    </div>

@endsection