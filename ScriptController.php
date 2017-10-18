<?php

namespace backend\controllers;

use app\models\HISTORY;
use app\models\HistorySearch;
use app\models\QUOTATION;
use app\models\QuotationSearch;
use app\models\SCRIPTCONTENT;
use app\models\TENTHOUSANDQUESTIONSEARCH;
use app\models\THISDAYHISTORY;
use app\models\ThisDayHistorySearch;
use Yii;
use app\models\SCRIPT;
use app\models\ScriptSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\log\Logger;

/**
 * ScriptController implements the CRUD actions for SCRIPT model.
 */
class ScriptController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all SCRIPT models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ScriptSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SCRIPT model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new SCRIPT model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SCRIPT();
        if($model->load(Yii::$app->request->post())){
            $rowData = SCRIPT::find()->where(['script_name'=>$model->script_name,'channel_id'=>$model->channel_id])->one();
            if(!$rowData){
                $audio_path_mp3 = $model->uploadMp3File($model, 'audio_path_mp3','audio_path_mp3','audio_path', 'script', 'mp3');
                if ($audio_path_mp3 == false) {
                    $model->addError('audio_path_mp3', 'Have error while upload file!');
                }
                if(!$model->save()){
                    $logger = \Yii::getLogger();
                    $logger->log("===SCRIPT===", Logger::LEVEL_ERROR);
                    $logger->log($model->getErrors(), Logger::LEVEL_ERROR);
                }else{
                    return $this->redirect(['index']);
                }
            }else{
                Yii::$app->session->setFlash('error', "Dữ liệu đã tồn tại");
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }else{
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
    public function actionRemoveScript(){
        $request =Yii::$app->request->post();
        if(isset($request['selected'])){
            foreach ($request['selected'] as $k=>$v){
                Yii::$app->db->createCommand("DELETE FROM SCRIPT_CONTENT WHERE id=".$v)->execute();
            }
        }
        $res['code'] = 0;
        $res['message'] ="Ok";
        return json_encode($res);

    }
    public function actionAddScript(){
        $request =Yii::$app->request->post();
        if(isset($request['selected'])){
            foreach ($request['selected'] as $value){
                $row =Yii::$app->db->createCommand("SELECT content_order FROM SCRIPT_CONTENT WHERE 1 ORDER BY content_order DESC")->queryOne();
                $ScriptContent = new SCRIPTCONTENT();
                $ScriptContent->content_id=$value;
                $ScriptContent->script_id = $request['id'];
                $ScriptContent->created_at =date('Y-m-d H:i:j');
                $ScriptContent->content_order =$row['content_order']+1;
                $ScriptContent->save();
            }
        }
        $res['code'] = 0;
        $res['message'] ="Ok";
        return json_encode($res);

    }
    public function actionListView(){
        $model = new SCRIPT();
        $searchModel = new SCRIPTCONTENT();
        $request =Yii::$app->request->post();
        if(isset($_POST['subm'])){
            $output = $_POST['output'];
            $arrOutput = json_decode($output, true);
//            var_dump($arrOutput); die();
            $order =count($arrOutput);
            for($i = 0; $i <= count($arrOutput)-1;$i++){
                Yii::$app->db->createCommand("UPDATE SCRIPT_CONTENT SET content_order=".$order." WHERE id=".$arrOutput[$i]['id'])->execute();
                $order--;
            }
        }
        return $this->redirect(['list', 'id' => $request['id'],'type'=>$request['type']]);
    }
    public function actionList($id,$type){
        $model = $this->findModel($id);
        $request =Yii::$app->request->post();
        if(isset($request['ids'])){
            foreach ($request['ids'] as $k=>$v){
                Yii::$app->db->createCommand("UPDATE SCRIPT_CONTENT SET content_order=".$request['content_order'][$k]." WHERE id=".$request['ids'][$k])->execute();
            }
            header("location:".$_SERVER['HTTP_REFERER']);
        }
        if($type == 0) {
            $searchModel = new SCRIPTCONTENT();
            $dataProvider = $searchModel->getListContentById($id);
//            return $this->render('list', [
//                'searchModel' => $searchModel,
//                'dataProvider' => $dataProvider,
//                'model'=>$model,
//                'id'=>$id,
//                'type'=>$type
//            ]);
        } elseif($type == 3) {
            $searchModel = new SCRIPTCONTENT();
            $dataProvider = $searchModel->getListContentThisDayHistoryById($id);
//            return $this->render('list-thisdayhistory', [
//                'searchModel' => $searchModel,
//                'dataProvider' => $dataProvider,
//                'model'=>$model,
//                'id'=>$id,
//                'type'=>$type
//            ]);
        } elseif($type == 1) {
            $searchModel = new SCRIPTCONTENT();
            $dataProvider = $searchModel->getListContentTenThousansQuestionById($id);
//            echo ($searchModel::getContentOrder(59,15)->content_order);
//            $dataProvider->models;
//            print_r($dataProvider->models);exit;
//            echo $id;
//            return $this->render('list-tenthousandquestion', [
//                'searchModel' => $searchModel,
//                'dataProvider' => $dataProvider,
//                'model'=>$model,
//                'id'=>$id,
//                'type'=>$type
//            ]);
        } elseif($type == 6) {
            $searchModel = new SCRIPTCONTENT();
            $dataProvider = $searchModel->getListContentTenQuotationById($id);
//            return $this->render('list-quotation', [
//                'searchModel' => $searchModel,
//                'dataProvider' => $dataProvider,
//                'model'=>$model,
//                'id'=>$id,
//                'type'=>$type
//            ]);
        }
        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model'=>$model,
            'id'=>$id,
            'type'=>$type
        ]);
    }
    public function actionAdd($id,$type){
        $model = $this->findModel($id);
        if($type ==0){
            //Lich su
            $rowEx =SCRIPTCONTENT::find()->select('content_id')->where(['script_id'=>$id])->asArray()->all();
            $arrayFiler =[];
            if($rowEx){
                foreach ($rowEx as $value){
                    $arrayFiler[]=$value['content_id'] ;
                }
            }
            $searchModel = new HistorySearch();
            $dataProvider = $searchModel->addsearch(Yii::$app->request->queryParams,$arrayFiler);
            return $this->render('add', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'model'=>$model,
                'id'=>$id,
                'type'=>$type
            ]);
        }
        if($type ==6){
            //Danh ngon
            $rowEx =SCRIPTCONTENT::find()->select('content_id')->where(['script_id'=>$id])->asArray()->all();
            $arrayFiler =[];
            if($rowEx){
                foreach ($rowEx as $value){
                    $arrayFiler[]=$value['content_id'] ;
                }
            }
            $searchModel = new QuotationSearch();
            $dataProvider = $searchModel->addsearch(Yii::$app->request->queryParams,$arrayFiler);
            return $this->render('addQuotation', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'model'=>$model,
                'id'=>$id,
                'type'=>$type
            ]);
        }
        if($type ==2){
            //Trac nghiem
            echo 'Chưa co';
        }
        if($type ==1){
            //10 van cau hoi
            $rowEx =SCRIPTCONTENT::find()->select('content_id')->where(['script_id'=>$id])->asArray()->all();
            $arrayFiler =[];
            if($rowEx){
                foreach ($rowEx as $value){
                    $arrayFiler[]=$value['content_id'] ;
                }
            }
            $searchModel = new TENTHOUSANDQUESTIONSEARCH();
            $dataProvider = $searchModel->addsearch(Yii::$app->request->queryParams,$arrayFiler);
            return $this->render('addTenThousend', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'model'=>$model,
                'id'=>$id,
                'type'=>$type
            ]);
        }
        if($type ==3){
            //Ngay nay nam xua
            $rowEx =SCRIPTCONTENT::find()->select('content_id')->where(['script_id'=>$id])->asArray()->all();
            $arrayFiler =[];
            if($rowEx){
                foreach ($rowEx as $value){
                    $arrayFiler[]=$value['content_id'] ;
                }
            }
            $searchModel = new ThisDayHistorySearch();
            $dataProvider = $searchModel->addsearch(Yii::$app->request->queryParams,$arrayFiler);
            return $this->render('addThisDayHistory', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'model'=>$model,
                'id'=>$id,
                'type'=>$type
            ]);
        }
    }
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldMp3 =$model->audio_path_mp3;
        $oldAudio =$model->audio_path;
        if($model->load(Yii::$app->request->post())){
            $rowData =SCRIPT::find()->where(['script_name'=>$model->script_name,'channel_id'=>$model->channel_id])->andWhere("id !=".$id)->one();
            if(!$rowData){
                $audio_path_mp3 = $model->uploadMp3File($model, 'audio_path_mp3','audio_path_mp3','audio_path', 'script', 'mp3');
                if ($audio_path_mp3 == false) {
                    $model->audio_path_mp3 =$oldMp3;
                }else{
                    if($oldMp3){
                        @unlink($uploadBasePath.$oldMp3);
                    }
                    if($oldAudio){
                        @unlink($uploadBasePath.$oldAudio);
                    }
                }
                if(!$model->save()){
                    $logger = \Yii::getLogger();
                    $logger->log("===Category===", Logger::LEVEL_ERROR);
                    $logger->log($model->getErrors(), Logger::LEVEL_ERROR);
                }else{
                    return $this->redirect(['index']);
                }
            }else{
                Yii::$app->session->setFlash('error', "Dữ liệu đã tồn tại");
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }else{
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing SCRIPT model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the SCRIPT model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SCRIPT the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SCRIPT::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
