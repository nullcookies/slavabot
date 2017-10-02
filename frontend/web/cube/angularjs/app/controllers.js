angular.module('cubeWebApp')
    .controller('dashboardCtrl', function ($scope, $http) {

        $scope.category = [];
        $scope.location = [];
        $scope.priority = [];
        $scope.theme = [];
        $scope.webhooks = 0;

        $http({method: 'GET', url: '/site/main'}).
        then(function success(response) {
            console.log(response);

            $scope.category = response.data.category;
            $scope.location = response.data.location;
            $scope.priority = response.data.priority;
            $scope.theme = response.data.theme;
            $scope.webhooks = response.data.webhooks;
        });
    })
    .controller('potentialCtrl', function ($scope, $http, $sce) {
        $scope.webhooks = [];

        $scope.sce = $sce;
        var vm = this;

        moment.locale('ru');

        $scope.search   = '';

        $scope.time = moment(new Date());

        function load(){
            $http({method: 'GET', url: '/potential/list'}).then(function success(response) {
                $scope.webhooks = response.data.webhooks;
                console.log('GETDATA');
            });
        }

        load();

        setInterval(load, 10000);

    });






