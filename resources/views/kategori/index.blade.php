@extends('adminlte::page')

@section('title', 'Kategori')

@section('content_header')

<meta name="csrf-token" content="{{ csrf_token() }}">
    <h1>Kategori</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <x-adminlte-button label="Add" theme="primary" icon="fas fa-plus" class="mb-3 float-right" data-toggle="modal" data-target="#addKategoriModal" value="add" id="addData"/>
    </div>
</div>
<div id="notif"></div>

<table id="kategoriTable" class="table">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Jenis</th>
            <th>Created</th>
            <th>Updated</th>
            <th>Actions</th>
        </tr>
    </thead>
</table>


<div class="modal fade" id="addKategoriModal" tabindex="-1" aria-labelledby="addKategoriModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addKategoriModalLabel">Add Kategori</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <form id="addKategoriForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-group">
                            <label>Jenis</label>
                            <select class="custom-select" name="jenis" id="jenis">
                                <option value="income">Income</option>
                                <option value="expense">Expense</option>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="action" id="action" value="Add" />
                    <input type="hidden" name="hidden_id" id="hidden_id" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="tambah btn btn-primary">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="ModalLabel" aria-hidden="true">
    <div class="modal-dialog">
    <div class="modal-content">
    <form method="post" id="sample_form" class="form-horizontal">
        <div class="modal-header">
            <h5 class="modal-title" id="ModalLabel">Confirmation</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
        </div>
        <div class="modal-body">
            <h5 align="center" style="margin:0;">Are you sure you want to remove this data?</h5>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" name="ok_button" id="ok_button" class="btn btn-danger">OK</button>
        </div>
    </form>  
    </div>
    </div>
</div>
@stop

@section('plugins.Datatables', true)
@section('js')
<script>
    $(function() {
        var i = 1;
        var table = $('#kategoriTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('kategori.index') }}",
        columns: [
            {
                data: 'iteration',
                name: 'iteration',
                orderable: false,
                searchable: false
            },
            {data: 'nama', name: 'nama'},
            {data: 'jenis', name: 'jenis'},
            {data: 'created_at', name: 'created_at'},
            {data: 'updated_at', name: 'updated_at'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ]
    });

        $('#addData').click(function(){
            $('#action').val('Add'); 
            $('.tambah').html("Add");
            $('#addKategoriForm')[0].reset();
        });

        $('#addKategoriForm').submit(function(e) {
            e.preventDefault();
            var action_url = '';
            var html = '';
            var id = '';
            
            if($('#action').val() == 'Add')
            {
                action_url = "{{ route('kategori.store') }}";
            }
    
            if($('#action').val() == 'Edit')
            {
                action_url = "{{ route('kategori.update') }}";
            }

            $.ajax({
                url: action_url,
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                type: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    $('#addKategoriForm')[0].reset();
                    $('#addKategoriModal').modal('hide');
                    if (response.success) {
                        html = '<div class="alert alert-success alert-dismissible">'
                        html += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'
                        html += response.message + '</div>'
                    } else {
                        html = '<div class="alert alert-danger alert-dismissible">'
                        html += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'
                        html += 'Failed</div>'
                    }
                    $('#notif').html(html);
                    table.draw();
                }
            });
        });

        $(document).on('click', '.edit', function(event){
            event.preventDefault(); 
            $('#addKategoriModal').modal('show');
            var id = $(this).attr('id');
    
            $.ajax({
                url :"/kategori/edit/"+id,
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                dataType:"json",
                success:function(data)
                {
                    console.log(data.result);
                    $('#nama').val(data.result.nama);
                    $('#jenis').val(data.result.jenis).change();
                    $('#hidden_id').val(data.result.id);
                    $('#action').val('Edit'); 
                    $('.tambah').html("Save");
                },
                error: function(data) {
                    var errors = data.responseJSON;
                    console.log(errors);
                }
            })
        });

        var kategori_id;
 
        $(document).on('click', '.hapus', function(){
            kategori_id = $(this).attr('id');
            $('#confirmModal').modal('show');
        });

        $('#ok_button').click(function(){
            $.ajax({
                url:"kategori/destroy/"+kategori_id,
                beforeSend:function(){
                    $('#ok_button').text('Deleting...');
                },
                success:function(response)
                {
                    if (response.success) {
                        html = '<div class="alert alert-success alert-dismissible">'
                        html += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'
                        html += response.message + '</div>'
                    } else {
                        html = '<div class="alert alert-danger alert-dismissible">'
                        html += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'
                        html += 'Failed</div>'
                    }
                    setTimeout(function(){
                    $('#ok_button').text('Ok');
                    $('#confirmModal').modal('hide');
                    $('#notif').html(html);
                    table.draw();
                    }, 500);
                }
            })
        });
    });
</script>
@stop