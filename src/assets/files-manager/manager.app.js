var app = angular.module('App', ['ui.bootstrap', 'ngSanitize', 'oitozero.ngSweetAlert', 'ui.uploader']);
app.filter('bytes', function() {
    return function(bytes, precision) {
        if (isNaN(parseFloat(bytes)) || !isFinite(bytes)) return '-';
        if (typeof precision === 'undefined') precision = 1;
        var units = ['bytes', 'kB', 'MB', 'GB', 'TB', 'PB'],
            number = Math.floor(Math.log(bytes) / Math.log(1024));
        return (bytes / Math.pow(1024, Math.floor(number))).toFixed(precision) +  ' ' + units[number];
    }
});
app.service('Files', function(){
    return {
        "selected": [],
        "item_files": [],
        "selected_ids": []
    };
});
app.run(['$http', 'CSRF_TOKEN', function($http, CSRF_TOKEN) {
    $http.defaults.headers.common['X-Csrf-Token'] = CSRF_TOKEN;
}]);
app.controller('filesController', function($scope, $http, SweetAlert, uiUploader,CSRF_TOKEN, $window, Files) {
    $scope.directories = [];
    $scope.files = [];
    $scope.user_files = Files;
    $scope.selected_directory = {};
    $scope.treeHtml = '';
    $scope.files_loading = false;
    $scope.files_for_upload = [];


    $http.get('/admin/files/api/directories')
        .then(function (response) {
            $scope.directories = response.data;
            $scope.selected_directory = $scope.directories[0];
            $scope.treeHtml = $scope.generateDirectoriesTree();
            $('body').on('click', '[data-ng-bind-html="treeHtml"] a', function(){
                var dir_id = $(this).attr('href').substr(1);
                $scope.files_loading = true;
                $scope.selected_directory.id = dir_id;
                $http.get('/admin/files/api/files', {
                    params: {
                        directory: dir_id
                    }
                }).then(function(response){
                    $scope.files_loading = false;
                    $scope.files = response.data;
                });
            });
        });

    /**
     * Update Files on change
     */
    $scope.$watch($scope.user_files, function(newVal, oldVal){
        Files = $scope.user_files;
    }, true);

    $scope.reloadStructure = function(){
        $http.get('/admin/files/api/directories')
            .then(function (response) {
                $scope.directories = response.data;
                $scope.treeHtml = $scope.generateDirectoriesTree();
                $http.get('/admin/files/api/files', {
                    params: {
                        directory: $scope.selected_directory.id
                    }
                }).then(function(response){
                    $scope.files_loading = false;
                    $scope.files = response.data;
                });
            });
    };
    $scope.downloadSelectedFile = function(){
        $window.open('/admin/files/download?file='+$scope.selected_files.id, '_blank');
    };
    $scope.selectFile = function(idx, event){
        if(event.ctrlKey){
            $scope.user_files.selected.push($scope.files[idx]);
            $scope.user_files.selected_ids.push($scope.files[idx].id)
        }else{
            $scope.user_files.selected = [$scope.files[idx]];
            $scope.user_files.selected_ids = [$scope.files[idx].id];
        }
    };
    $scope.generateDirectoriesTree = function(){
        var data = $(document.createElement('div'));
        angular.forEach($scope.directories, function(dir, index){
            var div = $(document.createElement('div'));
            var a = $(document.createElement('a')).attr({
                'href': '#'+dir.id
            }).addClass('list-group-item').text(' ' + dir.path);
            var fa = $(document.createElement('i')).addClass('fa');
            if(index===0){
                fa.addClass('fa-home');
            }else{
                fa.addClass('fa-folder');
            }
            a.prepend(fa);
            div.append(a);
            if(dir.children.length){
                div.append($scope.generateDirTree(dir.children, 0));
            }

            data.append(div);
        });
        return data.html();
    };

    $scope.generateDirTree = function(items, level){

        var div = $(document.createElement('div')).addClass('list-group col-xs-offset-'+parseInt(level+1));
        angular.forEach(items, function(dir, index){

            var a = $(document.createElement('a')).attr({
                'href': '#'+dir.id
            }).addClass('list-group-item').text(' '+dir.path);
            var fa = $(document.createElement('i')).addClass('fa fa-folder');
            a.prepend(fa);

            a.appendTo(div);
            if(dir.children.length){
                div.append($scope.generateDirTree(dir.children, parseInt(level+1)));
            }
        });
        return div;
    };

    $scope.sync = function(){
        $http.get('/admin/files/api/sync')
            .then(function(response){
                $scope.reloadStructure();
            }, function(error){
                SweetAlert.swal("Error "+error.status, error.statusText, "error");
            });
    };

    $scope.deleteFile = function(idx){
        var delete_file = $scope.files[idx];
        SweetAlert.swal({
                title: "Are you sure?",
                text: "Your will not be able to recover this file!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                closeOnConfirm: true},
            function(isConfirm){
                if(isConfirm){
                    $http.get('/admin/files/api/files/delete', {
                        params: {
                            id: delete_file.id
                        }
                    }).then(function (response) {
                        $scope.files.splice(idx, 1);
                        SweetAlert.swal("File deleted.");
                    }, function(error){
                        SweetAlert.swal("Error "+error.status, error.statusText, "error");
                    });
                }
            });
    };

    document.getElementById('file_upload').addEventListener('change', function(e) {
        var files = e.target.files;
        uiUploader.addFiles(files);
        $scope.files_for_upload = uiUploader.getFiles();
        $scope.$apply();
    });

    $scope.startUpload = function(){
        uiUploader.startUpload({
            url: '/admin/files/api/files',
            concurrency: 2,
            data: {
                'directories_id': $scope.selected_directory.id
            },
            headers: {
                'X-Csrf-Token': CSRF_TOKEN
            },
            onProgress: function(file) {
                $scope.files.push(file);
                $scope.$apply();
            },
            onCompletedAll: function(file, response) {
                $scope.clearFiles();
                $http.get('/admin/files/api/files', {
                    params: {
                        directory: $scope.selected_directory.id
                    }
                }).then(function(response){
                    $scope.files_loading = false;
                    $scope.files = response.data;
                });
            }
        });
    };

    $scope.editFolderName = '';
    $scope.saveFolder = function(){
        if($scope.editFolderName.length){
            $http({
                url: '/admin/files/api/directories/',
                method: 'POST',
                data: {
                    name: $scope.editFolderName,
                    parent_id: $scope.selected_directory.id
                }
            }).then(function () {
                $scope.reloadStructure();
            });
        }
    };
    $scope.clearFiles = function(){
        uiUploader.removeAll();
    };
    $scope.removeFile = function(file){
        uiUploader.removeFile(file);
    };
});
app.controller('filesContainerController', function($scope, $http, SweetAlert, CSRF_TOKEN, $window, Files) {

    $scope.files = Files;
    $scope.files_loading = false;


    $scope.$watch($scope.files, function(newVal, oldVal){
        Files = $scope.files;
    }, true);
    $scope.addSelectedFiles = function(){
        angular.forEach($scope.files.selected, function(item, key){
            $scope.files.item_files.push(item);
        });
        $scope.files.selected_ids = [];
        $scope.files.selected = [];
        $('#filesModal').modal('hide');
    };


});


// angular.bootstrap(document, ['App']);