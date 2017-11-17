
function getCSRF() {
    var metas = document.getElementsByTagName('meta');
    for (var i=0; i<metas.length; i++) {
        if (metas[i].getAttribute("name") ==="csrf-token") {
            return metas[i].getAttribute("content");
        }
    }
    return "";
}

function checkData($str, $len){
    return $str.length>$len;
}


angular.module('cubeWebApp')
    .controller('dashboardCtrl', function ($scope, $http) {

        function pad(number, length){
            var str = "" + number
            while (str.length < length) {
                str = '0'+str
            }
            return str
        }

        var offset = new Date().getTimezoneOffset()
        offset = ((offset<0? '+':'-')+ // Note the reversed sign!
            pad(parseInt(Math.abs(offset/60)), 2)+
            pad(Math.abs(offset%60), 2))

        console.log(offset);

        $scope.category = [];
        $scope.location = [];
        $scope.priority = [];
        $scope.theme = [];
        $scope.webhooks = 0;

        $http({method: 'GET', url: '/site/main'}).
        then(function success(response) {
            $scope.indb = response.data.indb;
            $scope.vk = response.data.vk;
            $scope.ok = response.data.ok;
            $scope.fb = response.data.fb;
            $scope.twitter = response.data.twitter;
            $scope.inst = response.data.inst;
            $scope.norm = response.data.norm;

            $scope.webhooks = response.data.webhooks;

            $scope.dateConvert = function(myDate){
                myDate=myDate.split("-");

                var newDate=myDate[1]+"/"+myDate[0]+"/"+myDate[2];

                return new Date(newDate).getTime();
            }

            var array = [];

            console.log(response.data.data);

            for (var i = 0; i < response.data.data.length; i++) {
                array[i]=[$scope.dateConvert(response.data.data[i]['dateNorm']), parseInt(response.data.data[i]['cnt'])];
            }

            var series = new Array();

            series.push({
                data: array,
                color: '#e84e40',
                lines: {
                    show : true,
                    lineWidth: 3,
                },
                points: {
                    fillColor: "#e84e40",
                    fillColor: '#ffffff',
                    pointWidth: 1,
                    show: true
                },
                label: 'Клиенты'
            });

            $.plot("#graph-bar", series, {
                colors: ['#03a9f4', '#f1c40f', '#2ecc71', '#3498db', '#9b59b6', '#95a5a6'],
                grid: {
                    tickColor: "#f2f2f2",
                    borderWidth: 0,
                    hoverable: true,
                    clickable: true
                },
                legend: {
                    noColumns: 1,
                    labelBoxBorderColor: "#000000",
                    position: "ne"
                },
                shadowSize: 0,
                xaxis: {
                    mode: "time",
                    tickSize: [1, "day"],
                    tickLength: 0,
                    // axisLabel: "Date",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Open Sans, sans-serif',
                    axisLabelPadding: 10
                }
            });

            var previousPoint = null;
            $("#graph-bar").bind("plothover", function (event, pos, item) {
                if (item) {
                    if (previousPoint != item.dataIndex) {

                        previousPoint = item.dataIndex;

                        $("#flot-tooltip").remove();
                        var x = item.datapoint[0],
                            y = item.datapoint[1];

                        showTooltip(item.pageX, item.pageY, item.series.label, y );
                    }
                }
                else {
                    $("#flot-tooltip").remove();
                    previousPoint = [0,0,0];
                }
            });
        });


    })
    .controller('header', function ($scope, $http) {})
    .controller('menu', function ($scope, $http) {

        $scope.potentialSubMenu = [];

        $http({method: 'GET', url: '/potential/filters'}).then(function success(response) {
            $scope.potentialSubMenu = response.data;
        });
    })
    .controller('potentialCtrl', function ($scope, $http, $sce, $location, $interval) {
        $scope.webhooks = [];
        $scope.location;

        $scope.sce = $sce;
        $scope.firstLoad = true;
        moment.locale('ru');
        $scope.search   = '';
        $scope.filterName = '';
        $scope.currentPage = 0;
        $scope.pageSize = 10;
        $scope.nameError = false;
        $scope.noFilter = false;
        $scope.cityPlaceholder = 'Город';
        $scope.themePlaceholder = 'Тема';
        $scope.numberOfPages = 0;
        $scope.filterSuccess = false;
        $scope.filterError = false;

        $scope.changeFilter = function(){

            if($scope.city==''){
                delete $scope.city;
            }

            if($scope.theme==''){
                delete $scope.theme;
            }

            $scope.setPage(0);

        };
        $scope.time = moment(new Date());

        $scope.getContact = function(id){
            var data = $.param({'id' : id});
            var config = {
                headers : {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                }
            };

            $http.post('/potential/set-owner', data, config).then(function success(response) {
               $location.path('/potential/detail/'+id);
            });
        };
        $scope.getList = function(){

            $http.get('/potential/list').then(function success(response) {
                $scope.user = response.data.user;
                $scope.webhooks = response.data.webhooks.webhooks;
                $scope.locations = response.data.webhooks.location;
                $scope.themes = response.data.webhooks.theme;
                $scope.pages = response.data.webhooks.pages;
                $scope.numberOfPages = $scope.pages.totalCount / $scope.pageSize;
            });
        };

        $scope.getList();
        $scope.paginationBlock = function(n){
            if($scope.currentPage < 4 && n < 7){
                return true;
            }else{
                if($scope.currentPage >  n + 3 || $scope.currentPage <  n - 3){
                    return false;
                }else{
                    return true;
                }
            }

        };
        $scope.disabledBack = function() {
            if($scope.currentPage == 0){
                return false;
            }else{
                $scope.currentPage = $scope.currentPage-1
            }
        };
        $scope.saveFilter = function() {

            if($scope.filterName.length<3){
                $scope.nameError = true;
                return false;
            }

            if($scope.search.length==0 && $scope.location === undefined && $scope.theme === undefined){
                $scope.noFilter = true;
                return false;
            }

            $scope.nameError = false;
            $scope.noFilter = false;

            var data = $.param({
                name: $scope.filterName,
                search : $scope.search,
                location : $scope.location,
                theme : $scope.theme
            });

            var config = {
                headers : {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                }
            };

            $http.post('/potential/new-filter', data, config).then(function success(response) {
                if(response.data){
                    $scope.filterSuccess = true;
                    $scope.filterError = false;
                }else{
                    $scope.filterSuccess = true;
                    $scope.filterError = false;
                }
            });
        };
        $scope.disabledNext = function() {
            if($scope.currentPage >= $scope.numberOfPages() - 1){
                return false;
            }else{
                $scope.currentPage = $scope.currentPage+1
            }
        };
        $scope.setPage = function(n){

            $scope.pagination = {
                'search' : $scope.search,
                'city' : $scope.location,
                'theme' : $scope.theme,
                'page' : n
            };

            var data = $.param($scope.pagination);

            var config = {
                headers : {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                }
            };

            $http.post('/potential/list', data, config).then(function success(response) {
                $scope.firstLoad = false;
                $scope.webhooks = response.data.webhooks.webhooks;
                $scope.pages = response.data.webhooks.pages;
                $scope.numberOfPages = $scope.pages.totalCount / $scope.pageSize;

                $scope.currentPage = n;
            });
        };
        $scope.setPage($scope.currentPage);
        $scope.Timer = $interval(function () {
            $scope.setPage($scope.currentPage)
        }, 5000);

    })
    .controller('filterCtrl', function ($scope, $http, $sce, $routeParams,$location, $interval) {
    $scope.webhooks = [];
    $scope.locations = [];
    $scope.themes = [];
    $scope.city;

    $scope.sce = $sce;
    moment.locale('ru');
    $scope.search   = '';
    $scope.filterName = '';
    $scope.currentPage = 0;
    $scope.pageSize = 10;
    $scope.nameError = false;
    $scope.noFilter = false;
    $scope.cityPlaceholder = 'Город';
    $scope.themePlaceholder = 'Тема';
    $scope.numberOfPages = 0;
    $scope.notif = false;
    $scope.notifEmail = '';
    $scope.filterSuccess = false;
    $scope.filterError = false;

    $scope.changeFilter = function(){

        if($scope.city==''){
            delete $scope.city;
        }
        if($scope.theme==''){
                delete $scope.theme;
        }

        $scope.setPage(0);

    };
    $scope.time = moment(new Date());

    var id = $routeParams["id"];

    $http({method: 'GET', url: '/potential/filter?id='+id}).then(function success(response) {
        $scope.user = response.data.user;

        $scope.notifEmail = response.data.filter.email;

        $scope.search = response.data.filter.search;
        $scope.city = response.data.filter.location;
        $scope.theme = response.data.filter.theme;
        $scope.filterName = response.data.filter.name;

        $scope.locations = response.data.location;
        $scope.themes = response.data.theme;


        $scope.setPage(0);
    });

    $scope.getContact = function(id){
        var data = $.param({'id' : id});
        var config = {
            headers : {
                'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
            }
        };

        $http.post('/potential/set-owner', data, config).then(function success(response) {
            $location.path('/potential/detail/'+id);
        });
    };

    $scope.paginationBlock = function(n){
        if($scope.currentPage < 4 && n < 7){
            return true;
        }else{
            if($scope.currentPage >  n + 3 || $scope.currentPage <  n - 3){
                return false;
            }else{
                return true;
            }
        }

    };
    $scope.disabledBack = function() {
        if($scope.currentPage == 0){
            return false;
        }else{
            $scope.currentPage = $scope.currentPage-1
        }
    };

    $scope.saveFilter = function() {
        if($scope.filterName.length<3){
            $scope.nameError = true;
            return false;
        }

        if($scope.search.length==0 && $scope.city === undefined && $scope.theme === undefined){
            $scope.noFilter = true;
            return false;
        }

        $scope.nameError = false;
        $scope.noFilter = false;

        var data = $.param({
            id: id,
            name: $scope.filterName,
            search: $scope.search,
            city: $scope.city,
            theme: $scope.theme,
            email : $scope.notifEmail,
        });

        var config = {
            headers : {
                'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
            }
        };

        $http.post('/potential/update-filter', data, config).then(function success(response) {
            if(response.data){
                $scope.filterSuccess = true;
                $scope.filterError = false;
            }else{
                $scope.filterSuccess = true;
                $scope.filterError = false;
            }
        });
    };
    $scope.disabledNext = function() {
        if($scope.currentPage >= $scope.numberOfPages() - 1){
            return false;
        }else{
            $scope.currentPage = $scope.currentPage+1
        }
    };
    $scope.setPage = function(n){

        $scope.pagination = {
            'search' : $scope.search,
            'city' : $scope.city,
            'theme' : $scope.theme,
            'page' : n
        };

        var data = $.param($scope.pagination);
        var config = {
            headers : {
                'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
            }
        };

        $http.post('/potential/list', data, config).then(function success(response) {

            $scope.webhooks = response.data.webhooks.webhooks;
            $scope.pages = response.data.webhooks.pages;
            $scope.numberOfPages = $scope.pages.totalCount / $scope.pageSize;
            $scope.currentPage = n;
        });
    };
        $scope.Timer = $interval(function () {
            $scope.setPage($scope.currentPage)
        }, 10000);
})
    .controller('detailCtrl', function($scope, $http, $routeParams, $sce,$location){
        console.log($location.path());
        $scope.webhook = [];
        $scope.sce = $sce;
        moment.locale('ru');
        $scope.getDetail = function(n){

            var data = $.param({'id' : $routeParams["id"]});
            var config = {
                headers : {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                }
            };

            $http.post('/potential/detail', data, config).then(function success(response) {
                $scope.user = response.data.user;
                $scope.webhook = response.data.webhooks.webhooks;
                console.log($scope.webhook);
            });
        };

        $scope.getDetail();
    })
    .controller('contactsCtrl', function ($scope, $http, $sce) {
        $scope.webhooks = [];
        $scope.city;

        $scope.sce = $sce;
        moment.locale('ru');
        $scope.search   = '';
        $scope.filterName = '';
        $scope.currentPage = 0;
        $scope.pageSize = 10;
        $scope.nameError = false;
        $scope.noFilter = false;
        $scope.cityPlaceholder = 'Город';
        $scope.themePlaceholder = 'Тема';
        $scope.numberOfPages = 0;

        $scope.changeFilter = function(){

            if($scope.city==''){
                delete $scope.city;
            }

            if($scope.theme==''){
                delete $scope.theme;
            }

            $scope.setPage(0);

        };
        $scope.time = moment(new Date());

        $http({method: 'GET', url: '/potential/contacts'}).then(function success(response) {
            $scope.user = response.data.user;
            $scope.webhooks = response.data.webhooks.webhooks;
            $scope.locations = response.data.webhooks.location;
            $scope.themes = response.data.webhooks.theme;
            $scope.pages = response.data.webhooks.pages;
            $scope.numberOfPages = $scope.pages.totalCount / $scope.pageSize;
        });

        $scope.paginationBlock = function(n){
            if($scope.currentPage < 4 && n < 7){
                return true;
            }else{
                if($scope.currentPage >  n + 3 || $scope.currentPage <  n - 3){
                    return false;
                }else{
                    return true;
                }
            }

        };
        $scope.disabledBack = function() {
            if($scope.currentPage == 0){
                return false;
            }else{
                $scope.currentPage = $scope.currentPage-1
            }
        };
        $scope.saveFilter = function() {
            $scope.arrFilter = {
                'search' : $scope.search,
                'city' : $scope.city,
                'theme' : $scope.theme
            };

            if($scope.filterName.length<3){
                $scope.nameError = true;
                return false;
            }

            if($scope.search.length==0 && $scope.city === undefined && $scope.theme === undefined){
                $scope.noFilter = true;
                return false;
            }

            $scope.nameError = false;
            $scope.noFilter = false;

            var data = $.param({
                name: $scope.filterName,
                filter: JSON.stringify($scope.arrFilter)
            });

            var config = {
                headers : {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                }
            }

            $http.post('/potential/new-filter', data, config).then(function success(response) {});
        };
        $scope.disabledNext = function() {
            if($scope.currentPage >= $scope.numberOfPages() - 1){
                return false;
            }else{
                $scope.currentPage = $scope.currentPage+1
            }
        };
        $scope.setPage = function(n){

            $scope.pagination = {
                'search' : $scope.search,
                'city' : $scope.city,
                'theme' : $scope.theme,
                'page' : n
            };

            var data = $.param($scope.pagination);

            var config = {
                headers : {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                }
            };

            $http.post('/potential/contacts', data, config).then(function success(response) {

                $scope.webhooks = response.data.webhooks.webhooks;
                $scope.pages = response.data.webhooks.pages;
                $scope.numberOfPages = $scope.pages.totalCount / $scope.pageSize;

                $scope.currentPage = n;
            });
        };
    })
    .controller('helpCtrl', function ($scope, $http, $sce) {

        $scope.helpMassage = "";
        $scope.helpMassageError = false;
        $scope.success = false;
        $scope.serverError = false;

        function getCSRF() {
            var metas = document.getElementsByTagName('meta');
            for (var i=0; i<metas.length; i++) {
                if (metas[i].getAttribute("name") ==="csrf-token") {
                    return metas[i].getAttribute("content");
                }
            }
            return "";
        }

        $scope.sendMassage = function(){
            var text = $scope.helpMassage;
            if(text.length > 6){
                $scope.helpMassageError = false;
                $scope.data = {
                    'text' : text,
                };

                var data = $.param($scope.data);

                var config = {
                    headers : {
                        'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;',
                        'X-CSRF-Token' : getCSRF()

                    }
                };

                $http.post('/system/help', data, config).then(function success(response) {
                    if(response){
                        $scope.success = true;
                        $scope.serverError = false;
                        $scope.helpMassageError = false;
                    }else{
                        $scope.serverError = true;
                    }
                });


            }else{
                $scope.helpMassageError = true;
            }
        };

    })
    .controller('configCtrl', function ($scope, $http, $sce) {

        $scope.mainPlane = true;
        $scope.passwordPlane = false;

        $scope.setMain = function(){
            $scope.mainPlane = true;
            $scope.passwordPlane = false;
        }
        $scope.setPassword = function(){
            $scope.mainPlane = false;
            $scope.passwordPlane = true;
        }

        $scope.username = '';
        $scope.phone = '';
        $scope.userSuccess = false;
        $scope.userError = false;
        $scope.password = '';
        $scope.new_password = '';
        $scope.new_password_repeat = '';

        function getCSRF() {
            var metas = document.getElementsByTagName('meta');
            for (var i=0; i<metas.length; i++) {
                if (metas[i].getAttribute("name") ==="csrf-token") {
                    return metas[i].getAttribute("content");
                }
            }
            return "";
        }

        function checkData($str, $len){
            return $str.length>$len;
        }

        var config = {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;',
                'X-CSRF-Token': getCSRF()
            }
        };

        $http.post('/site/user/', [], config).then(function success(response) {
            if (response) {
                $scope.username = response.data.username;
                $scope.phone = response.data.phone;
            } else {
                console.log('error');
            }
        });

        $scope.saveUser = function() {
            $scope.data = {
                'UserConfig[username]': $scope.username,
                'UserConfig[phone]': $scope.phone,
            };

            var data = $.param($scope.data);

            if(checkData($scope.username, 2) && checkData($scope.phone, 10)){

                $http.post('/site/config/', data, config).then(function success(response) {
                    if (response) {
                        $scope.userSuccess = true;
                        $scope.userError = false;
                    } else {
                        $scope.userSuccess = false;
                        $scope.userError = true;
                    }
                });
            }

        };

        $scope.savePassword = function() {

            console.log('Try to save new password');

            if(!checkData($scope.password, 2)){
                $scope.passwordError = true;

                console.log('Old password error');

                return false;
            }

            if(!checkData($scope.new_password, 3)){
                $scope.new_passwordError = true;

                console.log('New password error');

                return false;
            }

            if($scope.new_password!=$scope.new_password_repeat){
                $scope.new_password_repeatError = true;

                console.log('New password repeat error');

                return false;
            }


            $scope.passwordError = false;
            $scope.new_passwordError = false;
            $scope.new_password_repeatError = false;

            $scope.data = {
                'PasswordConfig[password]': $scope.password,
                'PasswordConfig[new_password]': $scope.new_password,
                'PasswordConfig[new_password_repeat]': $scope.new_password_repeat,
            };

            var data = $.param($scope.data);

            if(checkData($scope.password, 2) && checkData($scope.new_password, 3) && checkData($scope.new_password_repeat, 3) && $scope.new_password==$scope.new_password_repeat){

                $http.post('/site/config/', data, config).then(function success(response) {
                    if (response) {
                        $scope.userSuccess = true;
                        $scope.userError = false;
                    } else {
                        $scope.userSuccess = false;
                        $scope.userError = true;
                    }
                });
            }
        };
    })
    .controller('socialCtrl', function($scope, $http){

        $scope.InstaLogin = '';
        $scope.InstaPassword = '';
        $scope.accounts = [];
        $scope.unprocessed = [];
        $scope.unpID = 0;
        $scope.userSelection = {};
        $scope.accountData = {};
        $scope.unprocessedName;
        $scope.activeID;
        $scope.removingID = 0;

        var config = {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;',
                'X-CSRF-Token': getCSRF()
            }
        };

        $http.post('/social/unprocessed', [], config).then(function success(response) {
            if(response.data){
                $scope.unprocessed = response.data.data.groups;
                $scope.accountData = response.data.data;
                $scope.unpID = response.data.id;
                $scope.unprocessedType = response.data.type;
                $scope.unprocessedName = response.data.data.user_name;
                document.getElementById('getUnprocessed').click();
            }
        });

        $scope.getAccounts = function() {
            $http.post('/social/accounts', [], config).then(function success(response) {
                $scope.accounts = response.data;
                console.log($scope.accounts);
            });
        };

        $scope.remove = function() {
            if($scope.removingID>0){
                $id = $scope.removingID;
                $http.post('/social/remove', $.param({id: $id}), config).then(function success(response) {
                    if(response.data){
                        $scope.getAccounts();
                        $scope.clearInstaForm();
                        document.getElementById('closeConfirmModal').click();
                    }
                });
            }else{
                console.log('ID error!');
            }

        };

        $scope.showConfirm = function($id){
            $scope.removingID = $id;

            document.getElementById('confirmModal').click();
        };

        $scope.refresh = function($id, $type) {
            $http.post('/social/update-process', $.param({id: $id}), config).then(function success(response) {
                document.getElementById($type).click();
            });
        };

        $scope.instagramRefresh = function($account) {

            $scope.activeID = $account.id;
            $scope.InstaLogin = $account.data.login;
            $scope.InstaPassword = $account.data.password;

            document.getElementById('instagram').click();

            console.log($account);
        };

        $scope.clearInstaForm = function(){
            $scope.InstaLogin = '';
            $scope.InstaPassword = '';
            $scope.activeID = '';
            $scope.removingID = 0;
        };

        $scope.accountSave = function() {
            var data = $scope.accountData;

            data.groups = $scope.userSelection.activeValue;
            $scope.data = {
                id: $scope.unpID,
                data: data
            };

            $http.post('/social/finish-process', $.param($scope.data), config).then(function success(response) {
                if(response.data){
                    document.getElementById('closeModal').click();
                    $scope.getAccounts();
                }
            });
        };

        $scope.getAccounts();

        $scope.InstaSave = function(){

            if($scope.activeID>0){
                $scope.data = {
                    id: $scope.activeID,
                    type: 'instagram',
                    data: {
                        'login': $scope.InstaLogin,
                        'password': $scope.InstaPassword,
                    }
                };
            }else{
                $scope.data = {
                    type: 'instagram',
                    data: {
                        'login': $scope.InstaLogin,
                        'password': $scope.InstaPassword,
                    }
                };
            }

            if(checkData($scope.InstaLogin, 2) && checkData($scope.InstaPassword, 2)){
                $http.post('/social/instagram', $.param($scope.data), config).then(function success(response) {
                    document.getElementById('closeInstaModal').click();
                    $scope.clearInstaForm();
                    $scope.getAccounts();
                });
            }

        }
    })
    app.filter('startFrom', function() {
        return function(input, start) {
            start = +start; //parse to int
            return input.slice(start);
        }
    });

    app.filter('range', function() {
        return function(input, total) {
            total = Math.ceil(total);
            for (var i=0; i<total; i++) {
                input.push(i);
            }
            return input;
        };
    });




