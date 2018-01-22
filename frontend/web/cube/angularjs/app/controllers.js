
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

function checkArray($arr, $param, $value){
    $status = false;
    for (var i = 0; i < $arr.length; i++) {
        if(parseInt($arr[i][$param]) === parseInt($value)){
            return $arr[i];
        }
    }
    return $status;
}

angular.module('cubeWebApp')
    .controller('dashboardCtrl', function ($scope, $http, $sce, $interval) {
        var config = {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;',
                'X-CSRF-Token': getCSRF()
            }
        };

        $scope.vkLogin = '';
        $scope.vkPassword = '';
        $scope.vkError = false;
        $scope.vkLoginError = false;
        $scope.vkPasswordError = false;
        $scope.vkErrorText = '';
        $scope.vkAuthBox = true;
        $scope.vkGroupBox = false;
        $scope.vkfinish = false;
        $scope.userSelection = {};
        $scope.InstaLogin = '';
        $scope.InstaPassword = '';
        $scope.instaError = '';
        $scope.instaAuthBox = true;
        $scope.instafinish = false;
        $scope.sce = $sce;
        $scope.fbGroupBox = false;
        $scope.fbAuthBox = true;
        $scope.fbFinish = false;



        $scope.InstaSave = function(){
            $scope.data = {
                Instagram:{
                    type: 'instagram',
                    data: {
                        'login': $scope.InstaLogin,
                        'password': $scope.InstaPassword,
                    }
                }
            };

            if(checkData($scope.InstaLogin, 2) && checkData($scope.InstaPassword, 2)){
                $http.post('/social/instagram', $.param($scope.data), config).then(function success(response) {
                    if(response.data.error){
                        $scope.instaError = response.data.error;
                        return false;
                    }else{
                        $scope.instaError = '';
                        $scope.instaAuthBox = false;
                        $scope.instafinish = true;
                    }

                });
            }

        }

        $scope.checkUnprocessed = function(){
            $http.post('/social/unprocessed', [], config).then(function success(response) {
                if(response.data){
                    $scope.unprocessed = response.data.data.groups;
                    $scope.accountData = response.data.data;
                    $scope.unpID = response.data.id;
                    $scope.unprocessedType = response.data.type;
                    $scope.unprocessedName = response.data.data.user_name;
                    $scope.userSelection.activeValueVK = $scope.unprocessed[0].id;
                }
            });
        };

        $scope.checkUnprocessedFacebook = function(){
            $http.post('/social/unprocessed', { type : 'facebook'}, config).then(function success(response) {
                if(response.data.type=='facebook'){
                    $scope.unprocessed = response.data.data.groups;
                    $scope.accountData = response.data.data;
                    $scope.unpID = response.data.id;
                    $scope.unprocessedType = response.data.type;
                    $scope.unprocessedName = response.data.data.user_name;
                    $scope.fbGroupBox = true;
                    $scope.fbAuthBox = false;

                    console.log($scope.unprocessed);

                    $scope.userSelection.activeValueFB = $scope.unprocessed[0].id;
                }
            });
        };

        $scope.Timer = $interval(function () {
            if($scope.fbAuthBox){
                $scope.checkUnprocessedFacebook()
            }
        }, 1000);

        $scope.accountSave = function($type) {
            var data = $scope.accountData;

            if($type === 'vk'){
                data.groups = checkArray($scope.unprocessed, 'id',$scope.userSelection.activeValueVK);
            }else if($type === 'fb'){
                data.groups = checkArray($scope.unprocessed, 'id',$scope.userSelection.activeValueFB);
            }else{
                data.groups = $scope.userSelection.activeValue;
            }

            $scope.data = {
                id: $scope.unpID,
                data: data
            };

            $http.post('/social/finish-process', $.param($scope.data), config).then(function success(response) {
                if(response.data){
                    if($type=='vk'){
                        $scope.vkAuthBox = false;
                        $scope.vkGroupBox = false;
                        $scope.vkfinish = true;
                    }
                    if($type=='fb'){
                        $scope.fbGroupBox = false;
                        $scope.fbAuthBox = false;
                        $scope.fbFinish = true;
                    }
                }
            });
        };

        $scope.VkSave = function(){
            $scope.data = {
                login: $scope.vkLogin,
                password: $scope.vkPassword
            };

            if(checkData($scope.vkLogin, 9) && checkData($scope.vkPassword, 2)){
                $http.post('/social/vk-auth', $.param($scope.data), config).then(function success(response) {
                    console.log(response);

                    if(response.data.status){

                        $scope.vkError = false;
                        $scope.vkLoginError = false;
                        $scope.vkPasswordError = false;
                        $scope.vkErrorText = '';
                        $scope.checkUnprocessed();
                        $scope.vkAuthBox = false;
                        $scope.vkGroupBox = true;

                    }else{
                        if(response.data.error==='login & pass are wrong'){
                            $scope.vkError = true;
                            $scope.vkLoginError = false;
                            $scope.vkPasswordError = false;
                            $scope.vkErrorText = 'Неверный логин/пароль';
                        }
                    }

                });
            }else{
                if(!checkData($scope.vkLogin, 9)){
                    $scope.vkLoginError = true;
                }

                if(!checkData($scope.vkPassword, 4)){
                    $scope.vkPasswordError = true;
                }

            }
        }

        // Сохранение аккаунтов конец


    })

    .controller('header', function ($scope, $http, $interval) {
        $scope.telegramStatus = false;
        var config = {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;',
                'X-CSRF-Token': getCSRF()
            }
        };

        $scope.getUserData = function(){
            $http.post('/system/main-data', {}, config).then(function success(response) {
                $scope.telegramStatus = response.data.user.telegram;
                $scope.UserName = response.data.user.name;
            });
        };

        $scope.Timer = $interval(function () {
            $scope.getUserData()
        }, 5000);


        $scope.getUserData();
    })
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
        $scope.timezone = '';
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
                console.log(response);
                $scope.timezone = response.data.timezone;
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
                'UserConfig[timezone]': $scope.timezone,
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
    .controller('socialCtrl', function($scope, $http, $sce){

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

        $scope.vkLogin;
        $scope.vkPassword;
        $scope.vkError = false;
        $scope.vkLoginError = false;
        $scope.vkPasswordError = false;
        $scope.vkErrorText = '';
        $scope.instaError = '';
        $scope.activeVKID;
        $scope.sce = $sce;

        $scope.available = {
            'instagram' : true,
            'facebook' : true,
            'vkontakte' : true
        };

        var config = {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;',
                'X-CSRF-Token': getCSRF()
            }
        };

        $scope.checkUnprocessed = function(){
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
        };

        $scope.checkUnprocessed();

        $scope.getAccounts = function() {
            $http.post('/social/accounts', [], config).then(function success(response) {
                $scope.accounts = response.data;
                console.log($scope.accounts);
                $scope.available = {
                    'instagram' : true,
                    'facebook' : true,
                    'vkontakte' : true
                };
                for (key in $scope.accounts) {
                    $scope.available[$scope.accounts[key].type] = false;
                }
                console.log();
                console.log($scope.available);
            });
        }

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

        $scope.VKRefresh = function($account) {

            $scope.activeVKID = $account.id;
            $scope.vkLogin = $account.data.login;
            $scope.vkPassword = $account.data.password;

            document.getElementById('vkontakte').click();
        };

        $scope.clearInstaForm = function(){
            $scope.InstaLogin = '';
            $scope.InstaPassword = '';
            $scope.activeID = '';
            $scope.removingID = 0;
        };

        $scope.clearVkForm = function(){
            $scope.vkLogin = '';
            $scope.vkPassword = '';
        };
        $scope.getAccounts();

        $scope.VkSave = function(){

            if($scope.activeVKID>0){
                $scope.data = {
                    id: $scope.activeVKID,
                    login: $scope.vkLogin,
                    password: $scope.vkPassword
                };
            }else{
                $scope.data = {
                    login: $scope.vkLogin,
                    password: $scope.vkPassword
                };
            }

            if(checkData($scope.vkLogin, 9) && checkData($scope.vkPassword, 2)){
                $http.post('/social/vk-auth', $.param($scope.data), config).then(function success(response) {

                    console.log(response);

                    if(response.data.status){
                        $scope.vkError = false;
                        $scope.vkLoginError = false;
                        $scope.vkPasswordError = false;
                        $scope.vkErrorText = '';
                        document.getElementById('closemyModalVK').click();
                        $scope.clearVkForm();
                        $scope.getAccounts();
                        $scope.checkUnprocessed();
                    }else{
                        if(response.data.error==='login & pass are wrong'){
                            $scope.vkError = true;
                            $scope.vkLoginError = false;
                            $scope.vkPasswordError = false;
                            $scope.vkErrorText = 'Неверный логин/пароль';
                        }
                    }

                });
            }else{
                if(!checkData($scope.vkLogin, 9)){
                    $scope.vkLoginError = true;
                }

                if(!checkData($scope.vkPassword, 4)){
                    $scope.vkPasswordError = true;
                }

            }

        }

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

        $scope.InstaSave = function(){

            if($scope.activeID>0){
                $scope.data = {
                    Instagram :{
                        id: $scope.activeID,
                        type: 'instagram',
                        data: {
                            'login': $scope.InstaLogin,
                            'password': $scope.InstaPassword,
                        }
                    }
                };
            }else{
                $scope.data = {
                    Instagram :
                        {
                            type: 'instagram',
                            data: {
                                'login': $scope.InstaLogin,
                                'password': $scope.InstaPassword,
                            }
                        }
                };
            }

            if(checkData($scope.InstaLogin, 2) && checkData($scope.InstaPassword, 2)){
                $http.post('/social/instagram', $.param($scope.data), config).then(function success(response) {
                    if(response.data.error){
                        $scope.instaError = response.data.error;
                        return false;
                    }else{
                        $scope.instaError = '';
                    }
                    document.getElementById('closeInstaModal').click();
                    $scope.clearInstaForm();
                    $scope.getAccounts();
                });
            }

        }
    })
    .controller('historyCtrl', function($scope, $http, $sce, $interval){

        $scope.history = []; // Массив элементов истории
        $scope.order = 'desc'; // Сортировка по умолчанию
        $scope.planned = [];

        // Пагинация
        $scope.numberOfPages = 0; // Количество страниц
        $scope.currentPage = 0;   // Текущая страница
        $scope.pageSize = 10;     // Количество элементов на странице

        $scope.plannedCurrentPage = 0;   // Текущая страница

        //Вкладки
        $scope.allPlane = true;
        $scope.plannedPlane = false;

        $scope.setAll = function(){
            $scope.allPlane = true;
            $scope.plannedPlane = false;
        };
        $scope.setPlanned = function(){
            $scope.allPlane = false;
            $scope.plannedPlane = true;
        };

        moment.locale('ru'); // Локализация для отображения даты

        // Цепляем CSRF-Token для отправки post-запроса

        var config = {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;',
                'X-CSRF-Token': getCSRF()
            }
        };

        // Функция для обработки всех вариантов JSON, приходящих от бота.

        $scope.parJson = function (json) {
            var res;
            try{
                res = JSON.parse(JSON.parse(json));
            }catch(e){
                try{
                    res = JSON.parse(json);
                }catch(e){
                    res = json;
                }
            }
            return res;
        }
        $scope.fixMonth = function($mon){
            return $mon + 1;
        }
        $scope.getTime = function($time) {

            var date = new Date( $time * 1000 );

            var now = new Date();
            now = now.getDate() + '.' + $scope.fixMonth(now.getMonth()) + '.' + now.getFullYear();
            var day = date.getDate() + '.' + $scope.fixMonth(date.getMonth()) + '.' + date.getFullYear();
            var hours = date.getHours();

            hours = hours <10?'0'+hours:''+hours;

            var minutes = date.getMinutes();

            minutes = minutes <10?'0'+minutes:''+minutes;

            var seconds = date.getSeconds();

            seconds = seconds <10?'0'+seconds:''+seconds;

            if(now!=day){
                var time = day + ' ' + hours + ':' + minutes;
            }else{
                var time = hours + ':' + minutes;
            }


            return time;
        }

        // Получить элементы истории

        $scope.getList = function($data){
            $data = $data || [];
            console.log($data);
            $http.post('/history/get-list', $data, config).then(function success(response) {
                $scope.history = response.data.history;
                console.log($scope.history);
                $scope.pages = response.data.pages;
                $scope.numberOfPages = $scope.pages.totalCount / $scope.pageSize;
            });
        };

        // Получить запланированные
        $scope.getPlanned = function($data){
            $data = $data || [];
            $http.post('/history/get-planned', $data, config).then(function success(response) {
                $scope.planned = response.data.history;
                console.log($scope.planned);
                $scope.plannedPages = response.data.pages;
                $scope.plannedNumberOfPages = $scope.plannedPages.totalCount / $scope.pageSize;
            });
        };

        // Получаем при загрузке элементы истории

        //$scope.getList();

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
        $scope.changeOrder = function(){

            if($scope.order == 'desc'){
                $scope.order = 'asc';
            }else{
                $scope.order = 'desc';
            }
            $scope.setPlannedPage($scope.currentPage);
            $scope.setPage($scope.currentPage);
        };
        $scope.disabledBack = function() {
            if($scope.currentPage == 0){
                return false;
            }else{
                $scope.currentPage = $scope.currentPage-1
                $scope.setPage($scope.currentPage);
            }
        };

        $scope.disabledNext = function() {
            if($scope.currentPage >= $scope.numberOfPages - 1){
                return false;
            }else{
                $scope.currentPage = $scope.currentPage+1
                $scope.setPage($scope.currentPage);
            }
        };

        $scope.disabledPlaneBack = function() {
            if($scope.plannedCurrentPage == 0){
                return false;
            }else{
                $scope.plannedCurrentPage = $scope.plannedCurrentPage-1
                $scope.setPlannedPage($scope.plannedCurrentPage);
            }
        };

        $scope.disabledPlaneNext = function() {
            if($scope.plannedCurrentPage >= $scope.plannedNumberOfPages - 1){
                return false;
            }else{
                $scope.plannedCurrentPage = $scope.plannedCurrentPage+1
                $scope.setPlannedPage($scope.plannedCurrentPage);
            }
        };

        $scope.setPage = function(n){

            $scope.getList($.param({'page' : n, 'order' : $scope.order}));
            $scope.currentPage = n;
        };
        $scope.setPlannedPage = function(n){

            $scope.getPlanned($.param({'page' : n, 'order' : $scope.order}));
            $scope.plannedCurrentPage = n;
        };

        $scope.setPlannedPage($scope.currentPage);
        $scope.setPage($scope.currentPage);

        // $scope.Timer = $interval(function () {
        //     $scope.setPlannedPage($scope.currentPage);
        //     $scope.setPage($scope.currentPage);
        // }, 5000);

    })
    .controller('noticeCtrl', function($scope, $http, $sce, $interval){

        $scope.userNotice = '0';

        var config = {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;',
                'X-CSRF-Token': getCSRF()
            }
        };

        $scope.getNotifications = function(){
            $http.post('/notification/notifications', {}, config).then(function success(response) {
                console.log(response);
                $scope.userNotice = response.data;
            });
        };
        $scope.getNotifications();
    });
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




