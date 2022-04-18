@extends('layouts.app', ['title' => __('User Profile')])

@section('head')
    <link href="{{ asset('assets/vendor/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}"
          rel="stylesheet">
    <link href="{{ asset('assets/vendor/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}" rel="stylesheet">
@endsection
@section('content')
    <!-- Header -->
    <div class="header bg-primary pb-6 pt-6">
        <div class="container-fluid">
            <div class="header-body">
                <div class="row align-items-center py-4">
                    <div class="col-lg-6 col-7">
                        <h6 class="h2 text-white d-inline-block mb-0">@lang('cruds.user.title')</h6>
                        <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                            <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}"><i
                                            class="fas fa-home"></i></a></li>
                                <li class="breadcrumb-item active" aria-current="page">@lang('cruds.user.title')</li>
                            </ol>
                        </nav>
                    </div>
                    @can('users.create')
                        <div class="col-lg-6 col-5 text-right">
                            <a href="{{ route('user.create') }}" class="btn btn-sm btn-neutral">
                                <span class="fas fa-plus-circle"></span>
                                @lang('global.add')
                            </a>
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Page content -->
    <div class="container-fluid mt--6">
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="table-responsive py-4">
                        <table class="table table-striped table-bordered" id="example">
                            <thead class="thead-light">
                            <tr>
                                <th>@lang('cruds.user.fields.id')</th>
                                <th>@lang('cruds.user.fields.name')</th>
                                <th>@lang('cruds.user.fields.email')</th>
                                <th>@lang('cruds.user.fields.roles')</th>
                                <th>@lang('cruds.permission.fields.permissions')</th>
                                <th>@lang('global.actions')</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>@lang('cruds.user.fields.id')</th>
                                <th>@lang('cruds.user.fields.name')</th>
                                <th>@lang('cruds.user.fields.email')</th>
                                <th>@lang('cruds.user.fields.roles')</th>
                                <th>@lang('cruds.permission.fields.permissions')</th>
                                <th>@lang('global.actions')</th>
                            </tr>
                            </tfoot>
                            <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <th scope="row">{{ $user->id }}</th>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @foreach($user->roles as $role)
                                            <span class="badge bg-primary">{{ $role->name }}</span>
                                        @endforeach
                                    </td>
                                    <td style="white-space: inherit !important;">
                                        @foreach($user->getAllPermissions()->pluck('name') as $permission)
                                            <span class="badge badge-secondary">{{ $permission }} </span>
                                        @endforeach
                                    </td>
                                    <td class="table-actions text-center">
                                        @can('user.destroy')
                                            <form action="{{ route('user.destroy', $user->id) }}" method="post">
                                                @csrf
                                                @method("DELETE")
                                                <div class="btn-group">
                                                    @can('user.edit')
                                                        <a href="{{ route('user.edit', $user->id) }}"
                                                           class="table-action" data-toggle="tooltip"
                                                           data-original-title="Edit product">
                                                            <i class="fas fa-user-edit pr-1"> </i>
                                                        </a>
                                                    @endcan
                                                    <button type="button" class="p-0 table-action table-action-delete"
                                                            data-toggle="tooltip"
                                                            data-original-title="Delete product"
                                                            onclick="if (confirm('Вы уверены?')) { this.form.submit() } "
                                                            style="border: none;color:#5e72e4;cursor:pointer;:hover{border:none;color:#525f7f}">
                                                        <i class="fas fa-trash"> </i>
                                                    </button>
                                                </div>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

        <script src="{{ asset('assets/vendor/datatables.net/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatables.net-buttons/js/buttons.flash.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatables.net-buttons/js/buttons.colVis.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatables.net-select/js/dataTables.select.min.js') }}"></script>
        <script>
            $(document).ready(function () {
                var table = $('#example').DataTable({
                    lengthChange: false,
                    buttons: ['copy', 'excel', 'pdf', 'colvis']
                });

                table.buttons().container()
                    .appendTo('#example_wrapper .col-md-6:eq(0)');
            });
        </script>
    @endpush
@endsection
