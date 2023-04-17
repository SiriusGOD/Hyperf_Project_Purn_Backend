@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            @if(authPermission('redeem-create'))
                              <div class="col-sm-12 col-md-12">
                                  <a class="btn badge-info" href="/admin/redeem/create">{{trans('default.redeem.insert') ?? '新增優惠'}}</a>
                              </div>
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <table id="example2" class="table table-bordered table-hover dataTable dtr-inline"
                                       aria-describedby="example2_info">
                                    <thead>
                                    <tr>
                                        <th>{{trans('default.id') ?? '序號'}}</th>
                                        <th>{{trans('default.redeem.title') ?? '名稱'}}</th>
                                        <th>{{trans('default.redeem.category') ?? '種類'}}</th>
                                        <th>{{trans('default.redeem.count') ?? '可取領次數'}}</th>
                                        <th>{{trans('default.redeem.counted') ?? '己取領'}}</th>
                                        <th>{{trans('default.action') ?? '動作'}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($datas as $row)
                                        <tr class="odd">
                                            <td class="sorting_1 dtr-control">{{ $row->id }}</td>
                                            <td>{{ $row->title }}</td>
                                            <td>{{ isset($category[$row->category_id]) ? $category[$row->category_id]: "" }}</td>
                                            <td>{{ $row->count }}</td>
                                            <td>{{ $row->counted }}</td>
                                            <td>
                                                @if(authPermission('redeem-edit'))
                                                    <a href="/admin/redeem/edit?id={{$row->id}}" class="btn btn-primary">{{trans('default.edit') ?? '編輯'}}</a>
                                                @endif

                                                @if(authPermission('redeem-delete'))
                                                    <form action="/admin/redeem/delete" method="post" _method="delete">
                                                        <input type="hidden" name="_method" value="delete" >
                                                        <input type="hidden" name="id" value="{{$row->id}}" >
                                                        <input type="submit"  class="btn btn-danger" value="{{trans('default.redeem.delete') ?? '刪除'}}">
                                                    </form>
                                                @endif

                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <th>{{trans('default.id') ?? '序號'}}</th>
                                        <th>{{trans('default.redeem.title') ?? '名稱'}}</th>
                                        <th>{{trans('default.redeem.category') ?? '種類'}}</th>
                                        <th>{{trans('default.redeem.count') ?? '可取領次數'}}</th>
                                        <th>{{trans('default.redeem.counted') ?? '己取領'}}</th>
                                        <th>{{trans('default.action') ?? '動作'}}</th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="example2_info" redeem="status" aria-live="polite">
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
