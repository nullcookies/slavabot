<?
use frontend\controllers\SocialController;
?>

<div class="col-lg-12 ng-scope">
    <div class="main-box clearfix">
        <div class="main-box-body clearfix">
            <div class="row">
                <div class="col-md-12">
                    <div style="margin-top:25px;"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Привязать соц.сеть <span class="caret"></span></button>
                                <ul class="dropdown-menu accountsMenu" role="menu">
                                    <li><a href="#">Facebook</a></li>
                                    <li>
                                        <?=
                                            SocialController::getVKBtn(
                                                'http://'.$_SERVER['SERVER_NAME'].'/#/pages/social',
                                                'Вконтакте',
                                                \Yii::$app->user->identity->id
                                            );
                                        ?>
                                    </li>
                                    <li><a data-toggle="modal" data-target="#myModal">Instagram</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top:25px;"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <h2>Список привязанных аккаунтов:</h2>
                            <br>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th><span>Соц.сеть</span></th>
                                        <th><span>Имя аккаунта</span></th>
                                        <th class="text-center"><span>Статус</span></th>
                                        <th>
                                            Доступные аккаунты групп
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr ng-repeat="account in accounts">
                                        <td>
                                            {{account.type}}
                                        </td>
                                        <td>
                                            {{account.data.login}}
                                        </td>
                                        <td class="text-center">
                                            <span class="label label-success" ng-show="account.status === 1">Активна</span>
                                            <span class="label label-danger" ng-show="account.status === 0">Ошибка</span>
                                            <span class="label label-warning" ng-show="account.status === null">Проверка</span>
                                        </td>
                                        <td ng-show="account.type === 'instagram'">
                                            <table class="noTop">
                                                <tbody>
                                                <tr>
                                                    <td>
                                                        {{account.data.login}}
                                                    </td>
                                                    <td style="width: 15%;">
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-primary">Обновить</button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Добавить аккаунт Instagram</h4>
                    </div>
                    <div class="modal-body">
                        <form role="form" class="ng-pristine ng-valid">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Логин</label>
                                <input type="text" class="form-control" ng-model="InstaLogin" placeholder="Номер телефона, имя пользователя или эл. адрес">
                            </div>
                            <div class="form-group">
                                <label for="exampleInputPassword1">Пароль</label>
                                <input type="password" class="form-control" ng-model="InstaPassword" placeholder="Пароль">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" ng-click="InstaSave()">Сохранить</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
