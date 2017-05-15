<div class="panel panel-default" data-ng-controller="filesController">
    <div class="panel-heading">

        <div class="btn-group pull-left">
            <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false"><i class="fa fa-plus"></i> New
            </button>
            <ul class="dropdown-menu">
                <li><a href="#folderModal" data-toggle="modal"><i class="fa fa-folder"></i> New folder</a></li>
                {{--<li><a href="javascript:;"><i class="fa fa-file"></i> New file</a></li>--}}
            </ul>
        </div>
        <div class="btn-group pull-left" role="group" aria-label="Menu">
            <label for="file_upload" class="btn btn-sm btn-default btn-file"
                   data-ng-class="{'disabled': !selected_directory.id}" data-ng-disabled="!selected_directory.id">
                <i class="fa fa-upload"></i> Upload <input type="file" name="file" multiple id="file_upload"
                                                           style="display: none;">
            </label>
            <button type="button" class="btn btn-sm btn-default" data-ng-class="{'disabled': user_files.selected.length!==1}"
                    data-ng-disabled="user_files.selected.length!==1" data-ng-click="downloadSelectedFile()"><i
                        class="fa fa-download"></i> Download
            </button>
            {{--<button type="button" class="btn btn-sm btn-default" data-ng-class="{'disabled': user_files.selected.length!==1}"--}}
                    {{--data-ng-disabled="user_files.selected.length!==1"><i class="fa fa-pencil-square-o"></i> Edit--}}
            {{--</button>--}}
        </div>
        <div class="col-xs-5">
            <input type="search" class="form-control input-sm" placeholder="Filter" name="filter" id="files-filter">
        </div>
        <div class="btn-group pull-right" role="group">
            <button type="button" class="btn btn-sm btn-primary" data-ng-click="sync()"><i class="fa fa-refresh"></i>
            </button>
            <button type="button" class="btn btn-sm btn-danger confirm"><i class="fa fa-trash"></i></button>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="panel-body">

        <div class="list-group col-sm-3" data-ng-bind-html="treeHtml">

        </div>

        <div class="panel panel-default col-sm-9">
            <div class="panel-body">
                <div class="panel" data-ng-if="files_for_upload.length">
                    <div ng-repeat="file in files_for_upload"
                         style="margin-top: 20px;border-bottom-color: antiquewhite;border-bottom-style: double;">
                        <div><span>@{{file.name}}</span>
                            <div style="float:right;"><span>@{{file.humanSize}}</span><a
                                        data-ng-click="removeFile(file)" title="Remove from upload"><i
                                            class="icon-remove"></i></a></div>
                        </div>
                        <progress style="width:100%;" value="@{{file.loaded}}" max="@{{file.size}}"></progress>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" data-ng-click="startUpload()">Start upload
                        </button>
                        <button type="button" class="btn btn-danger" data-ng-click="clearFiles()">Remove files</button>
                    </div>
                </div>
                <ul class="list-inline files-list" data-ng-if="files.length && !files_loading">
                    <li class="item" data-ng-class="{selected: user_files.selected_ids.indexOf(file.id)!==-1}"
                        data-ng-repeat="file in files track by $index" data-ng-click="selectFile($index, $event)">

                        <div class="text-center image-block">
                            <div class="editor-block text-right">
                                <button type="button" class="btn btn-xs btn-primary" data-ng-click="editFile($index)"><i
                                            class="fa fa-pencil"></i></button>
                                <button type="button" class="btn btn-xs btn-danger" data-ng-click="deleteFile($index)">
                                    <i class="fa fa-trash"></i></button>
                            </div>
                            <img src="@{{ file.thumb.encoded }}" alt="@{{ file.filename }}">
                        </div>
                        <div class="help-block small" title="@{{ file.filename }} (@{{ file.file_size | bytes}})">
                            @{{ file.filename }} (@{{ file.file_size | bytes}})
                        </div>
                    </li>

                </ul>
                <div class="alert alert-info" data-ng-if="!files.length && !files_loading">
                    <p class="text-center">No files in directory</p>
                </div>
                <div class="alert alert-info" data-ng-if="files_loading==true">
                    <p class="text-center"><i class="fa fa-spinner fa-pulse fa-fw"></i> Loading files</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editImage">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Edit Image</h4>
                </div>
                <div class="modal-body">
                    Modal body ...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success">Save changes</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div class="modal fade" id="folderModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Create/Edit Directory</h4>
                </div>
                <div class="modal-body">
                    <input type="text" name="folderName" id="folderName" class="form-control" data-ng-model="editFolderName" title="Create/Edit Folder">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-ng-click="saveFolder()">Save</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
</div>
@section('css')
    <link href="{{asset('assets/laramod/dashboard/files-manager/style.css')}}" rel="stylesheet">
    <script src="{{asset('assets/laramod/dashboard/bower_components/angular-ui-uploader/dist/uploader.min.js')}}"></script>
    <script src="{{asset('assets/laramod/dashboard/files-manager/manager.app.js')}}"></script>
@stop
