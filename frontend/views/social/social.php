<?
use frontend\controllers\SocialController;
?>

<div class="col-lg-12 ng-scope">
    <div class="main-box clearfix">
        <div class="main-box-body clearfix">
            <div class="row">
                <div class="col-md-6">
                    <div style="margin-top:25px;"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Привязать соц.сеть <span class="caret"></span></button>
                                <ul class="dropdown-menu" role="menu">
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
                                    <li><a href="#">Instagram</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top:25px;"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <h2>Список привязанных аккаунтов:</h2>
                            <br>
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th><span>Соц.сеть</span></th>
                                    <th><span>Имя аккаунта</span></th>
                                    <th class="text-center"><span>Статус</span></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>
                                        Вконтакте
                                    </td>
                                    <td>
                                        Александр Горбачев
                                    </td>
                                    <td class="text-center">
                                        <span class="label label-danger">Ошибка</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Facebook
                                    </td>
                                    <td>
                                        Александр Горбачев
                                    </td>
                                    <td class="text-center">
                                        <span class="label label-warning">Проверка</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Instagram
                                    </td>
                                    <td>
                                        Александр Горбачев
                                    </td>
                                    <td class="text-center">
                                        <span class="label label-success">Активна</span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <header class="main-box-header clearfix">
                        <h2 class="pull-left">Доступные аккаунты групп</h2>
                    </header>

                    <div class="main-box-body clearfix">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th><span>Соц.сеть</span></th>
                                    <th><span>Название</span></th>
                                    <th>&nbsp;</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>
                                        Вконтакте
                                    </td>
                                    <td>
                                        Подслушано в Медвежьегорске
                                    </td>
                                    <td style="width: 15%;">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-primary">Привязать</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Вконтакте
                                    </td>
                                    <td>
                                        Подслушано в Медвежьегорске
                                    </td>
                                    <td style="width: 15%;">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-primary">Привязать</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Вконтакте
                                    </td>
                                    <td>
                                        Подслушано в Медвежьегорске
                                    </td>
                                    <td style="width: 15%;">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-primary">Привязать</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Вконтакте
                                    </td>
                                    <td>
                                        Подслушано в Медвежьегорске
                                    </td>
                                    <td style="width: 15%;">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-primary">Привязать</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Вконтакте
                                    </td>
                                    <td>
                                        Подслушано в Медвежьегорске
                                    </td>
                                    <td style="width: 15%;">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-primary">Привязать</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Вконтакте
                                    </td>
                                    <td>
                                        Подслушано в Медвежьегорске
                                    </td>
                                    <td style="width: 15%;">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-primary">Привязать</button>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
