<?php
/**
 * Acoes do modulo "Call".
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author  Adilson Leffa Magnus.
 * @copyright   Todos os direitos reservados.
 * ###################################
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 * 17/08/2012
 */

class CallSummaryController extends Controller
{
    public $attributeOrder = 'day DESC';
    public $limit          = 7;
    public $group          = 'day';
    public $select         = 'SQL_CACHE t.id, DATE(starttime) AS day, starttime,
            sum(sessiontime) AS sessiontime,
            sum(sessionbill) AS sessionbill,
            sum(buycost) AS buycost,
            count(*) as nbcall';

    public $fieldsInvisibleClient = array(
        'id',
        'id_user_package_offer',
        'id_did',
        'id_prefix',
        'id_ratecard',
        'id_tariffgroup',
        'id_trunk',
        'real_sessiontime',
        'root_cost',
        'sessionid',
        'sipiax',
        'src',
        'stoptime',
        'markup',
        'calledstation',
        'idCardusername',
        'idTrunktrunkcode',
        'id_user',
        'id_user',
        'lucro',
        'sumlucro',
        'sumbuycost',
        'buycost',
    );

    public $fieldsInvisibleAgent = array(
        'uniqueid',
        'id',
        'id_user_package_offer',
        'id_did',
        'id_prefix',
        'id_ratecard',
        'id_tariffgroup',
        'id_trunk',
        'real_sessiontime',
        'root_cost',
        'sessionid',
        'sipiax',
        'src',
        'stoptime',
        'markup',
        'calledstation',
        'idCardusername',
        'idTrunktrunkcode',
        'id_user',
        'id_user',
    );

    public function init()
    {
        $this->instanceModel = new CallSummary;
        $this->abstractModel = CallSummary::model();
        $this->titleReport   = Yii::t('yii', 'Calls Summary');
        parent::init();

        if (Yii::app()->session['isAgent'] == true) {
            $this->select = 'SQL_CACHE t.id, DATE(starttime) AS day, starttime,
            sum(sessiontime) AS sessiontime,
            sum(agent_bill) AS sessionbill,
            sum(sessionbill) AS buycost,
            count(*) as nbcall';
        } else if (Yii::app()->session['isClientAgent'] == true) {
            $this->select = 'SQL_CACHE t.id, DATE(starttime) AS day, starttime,
            sum(sessiontime) AS sessiontime,
            sum(agent_bill) AS sessionbill,
            count(*) as nbcall';
        } elseif (Yii::app()->session['isClient'] == true) {
            $this->select = 'SQL_CACHE t.id, DATE(starttime) AS day, starttime,
            sum(sessiontime) AS sessiontime,
            sum(sessionbill) AS sessionbill,
            count(*) as nbcall';
        }
    }

    public function actionRead($asJson = true, $condition = null)
    {
        # recebe os parametros para o filtro
        $filter = isset($_GET['filter']) ? $_GET['filter'] : null;
        $filter = $filter ? $this->createCondition(json_decode($filter)) : $this->defaultFilter;

        $limit = strlen($filter) > 2 && preg_match("/starttime/", $filter) ? $_GET[$this->nameParamLimit] : $this->limit;

        //nao permite mais de 31 registros
        $limit                       = $limit > 31 ? $limit                       = 31 : $limit;
        $_GET[$this->nameParamLimit] = $limit;

        parent::actionRead($asJson = true, $condition = null);

    }
    public function recordsExtraSum($select = '*', $join = '', $filter = '', $group = '', $limit = '', $records = array())
    {
        foreach ($records as $key => $value) {
            $records[0]->sumsessiontime += $value['sessiontime'] / 60;
            $records[0]->sumsessionbill += $value['sessionbill'];
            $records[0]->sumbuycost += $value['buycost'];
            $records[0]->sumaloc_all_calls += $value['sessiontime'] / $value['nbcall'];
            $records[0]->sumnbcall += $value['nbcall'];
        }

        $this->nameSum = 'sum';

        return $records;
    }

    public function getAttributesModels($models, $itemsExtras = array())
    {
        $attributes = false;
        foreach ($models as $key => $item) {
            $attributes[$key]                   = $item->attributes;
            $attributes[$key]['nbcall']         = $item->nbcall;
            $attributes[$key]['day']            = $item->day;
            $attributes[$key]['aloc_all_calls'] = $item->aloc_all_calls;

            $attributes[$key]['lucro'] = $item->sessionbill - $item->buycost;

            $attributes[$key]['sessiontime'] = $item->sessiontime / 60;

            $attributes[$key]['aloc_all_calls'] = $item->nbcall > 0
            ? $item->sessiontime / $item->nbcall
            : 0;

            $attributes[$key]['sumsessiontime']    = $item->sumsessiontime;
            $attributes[$key]['sumsessionbill']    = $item->sumsessionbill;
            $attributes[$key]['sumbuycost']        = $item->sumbuycost;
            $attributes[$key]['sumlucro']          = $item->sumlucro;
            $attributes[$key]['sumaloc_all_calls'] = $item->sumaloc_all_calls;
            $attributes[$key]['sumnbcall']         = $item->sumnbcall;
            $attributes[$key]['idCardusername']    = $item->idCardusername;
            $attributes[$key]['idTrunktrunkcode']  = $item->idTrunktrunkcode;

            if (isset(Yii::app()->session['isClient']) && Yii::app()->session['isClient']) {
                foreach ($this->fieldsInvisibleClient as $field) {
                    unset($attributes[$key][$field]);
                }
            }

            if (isset(Yii::app()->session['isAgent']) && Yii::app()->session['isAgent']) {
                foreach ($this->fieldsInvisibleAgent as $field) {
                    unset($attributes[$key][$field]);
                }
            }

            foreach ($itemsExtras as $relation => $fields) {
                $arrFields = explode(',', $fields);
                foreach ($arrFields as $field) {
                    $attributes[$key][$relation . $field] = $item->$relation->$field;
                    if (Yii::app()->session['idClient']) {
                        foreach ($this->fieldsInvisibleClient as $field) {
                            unset($attributes[$key][$field]);
                        }
                    }

                    if (Yii::app()->session['idAgent']) {
                        foreach ($this->fieldsInvisibleAgent as $field) {
                            unset($attributes[$key][$field]);
                        }
                    }
                }
            }
        }

        return $attributes;
    }

    public function filterReplace($filter)
    {

        if (preg_match('/c.username/', $filter)) {
            if (!preg_match("/JOIN pkg_user/", $this->join)) {
                $this->join .= ' LEFT JOIN pkg_user c ON t.id_user = c.id';
            }

            $filter = preg_replace('/c.username/', "c.username", $filter);
        }

        if (preg_match('/pkg_trunk.trunkcode/', $filter)) {
            if (!preg_match("/JOIN pkg_trunk/", $this->join)) {
                $this->join .= ' LEFT JOIN pkg_trunk ON t.id_trunk = pkg_trunk.id';
            }

        }

        return $filter;
    }

}
