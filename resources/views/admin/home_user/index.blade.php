@extends('admin.layouts.iframe')
@section('content')
    <div class="box">
        <div class="box-header with-border">
            <form action="" class="form-horizontal" autocomplete="off">
                <div class="form-group">
                    <div class="col-md-2 control-label">请输入</div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="key" value="{{request('key')}}"
                               placeholder="用户名/钱包ID">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-4 pull-right">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="box-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>用户名</th>
                    <th>钱包ID</th>
                    <td>状态</td>
                    <th>活跃时间</th>
                    <th>加入时间</th>
                    <td>操作</td>
                </tr>
                </thead>
                <tbody>
                @foreach($list as $item)
                    <tr>
                        <td>
                            {{ $item->name }}
                        </td>
                        <td>{{ $item->walletid }}</td>
                        <td>{{ $item->status == 1 ? '正常' : '冻结' }}</td>
                        <td>{{ $item->updated_at }}</td>
                        <td>{{ $item->created_at }}</td>
                        <td>
                            <a href="{{route('admin.home_user.edit', $item)}}" class="btn btn-info btn-sm">修改</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="box-footer clearfix">
            {{$list->appends(request()->all())->links()}}
        </div>
    </div>
@endsection
@section('script')

@endsection