<?php

namespace Workdo\Lead\Http\Controllers;

use App\Models\User;
use App\Models\WorkSpace;
use App\Models\EmailTemplate;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\FacadesAuth;
use Workdo\Lead\Entities\ClientDeal;
use Workdo\Lead\Entities\Deal;
use Workdo\Lead\Entities\DealCall;
use Workdo\Lead\Entities\DealDiscussion;
use Workdo\Lead\Entities\DealEmail;
use Workdo\Lead\Entities\DealFile;
use Workdo\Lead\Entities\DealStage;
use Workdo\Lead\Entities\DealTask;
use Workdo\Lead\Entities\Label;
use Workdo\Lead\Entities\Lead;
use Workdo\Lead\Entities\LeadActivityLog;
use Workdo\Lead\Entities\LeadCall;
use Workdo\Lead\Entities\LeadDiscussion;
use Workdo\Lead\Entities\LeadEmail;
use Workdo\Lead\Entities\LeadFile;
use Workdo\Lead\Entities\LeadStage;
use Workdo\Lead\Entities\Pipeline;
use Workdo\Lead\Entities\Source;
use Workdo\Lead\Entities\User as EntitiesUser;
use Workdo\Lead\Entities\UserDeal;
use Workdo\Lead\Entities\UserLead;
use Workdo\Lead\Entities\LeadUtility;
use Workdo\ProductService\Entities\ProductService;
use App\Models\Role;
use Illuminate\Support\Facades\Mail;
use Workdo\Lead\DataTables\LeadDataTable;
use Workdo\Lead\Entities\LeadTask;
use Workdo\Lead\Events\CreateLead;
use Workdo\Lead\Events\CreateLeadTask;
use Workdo\Lead\Events\DestroyLead;
use Workdo\Lead\Events\DestroyLeadCall;
use Workdo\Lead\Events\DestroyLeadFile;
use Workdo\Lead\Events\DestroyLeadProduct;
use Workdo\Lead\Events\DestroyLeadSource;
use Workdo\Lead\Events\DestroyLeadTask;
use Workdo\Lead\Events\DestroyLeadUser;
use Workdo\Lead\Events\LeadAddCall;
use Workdo\Lead\Events\LeadAddDiscussion;
use Workdo\Lead\Events\LeadAddEmail;
use Workdo\Lead\Events\LeadAddNote;
use Workdo\Lead\Events\LeadAddProduct;
use Workdo\Lead\Events\LeadAddUser;
use Workdo\Lead\Events\LeadConvertDeal;
use Workdo\Lead\Events\LeadMoved;
use Workdo\Lead\Events\LeadSourceUpdate;
use Workdo\Lead\Events\LeadUpdateCall;
use Workdo\Lead\Events\LeadUploadFile;
use Workdo\Lead\Events\StatusChangeLeadTask;
use Workdo\Lead\Events\UpdateLead;
use Workdo\Lead\Events\UpdateLeadTask;

class LeadController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function __construct()
    {

        if (module_is_active('GoogleAuthentication')) {
            $this->middleware('2fa');
        }
    }

    public function dashboard()
    {
        if (Auth::user()->isAbleTo('crm dashboard manage')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $transdate = date('Y-m-d', time());

            $calenderTasks = [];
            $chartData     = [];
            $chartcall     = [];
            $dealdata      = [];
            $stagedata     = [];
            $arrCount      = [];
            $arrErr        = [];
            $m             = date("m");
            $de            = date("d");
            $y             = date("Y");
            $format        = 'Y-m-d';
            $user          = Auth::user();

            $usr          =  EntitiesUser::find($user->id);
            if ($user->hasRole('company')) {
                //Handle Custom Error for System Setting

                foreach ($usr->deals as $deal) {
                    foreach ($deal->tasks as $task) {
                        $task = DealTask::where('id', $task->id)->where('workspace', $getActiveWorkSpace)->first();
                        if (!empty($task)) {

                            $calenderTasks[] = [
                                'title' => $task->name,
                                'start' => $task->date,
                                'url' => route(
                                    'deals.tasks.show',
                                    [
                                        $deal->id,
                                        $task->id,
                                    ]
                                ),
                                'className' => ($task->status) ? 'event-success border-success' : 'event-warning border-warning',
                            ];
                        } else {
                            $calenderTasks[] = [];
                        }
                    }
                }

                $arrCount['client']  = User::where('type', '=', 'client')->where('created_by', '=', $creatorId)->where('workspace_id', '=', $getActiveWorkSpace)->count();
                $arrCount['user']    = User::where('type', '!=', 'client')->where('created_by', $creatorId)->where('workspace_id', '=', $getActiveWorkSpace)->count();
                $arrCount['deal']    = Deal::where('created_by', '=', $creatorId)->where('workspace_id', '=', $getActiveWorkSpace)->count();
                $arryTemp = [];
                for ($i = 0; $i <= 7 - 1; $i++) {
                    $date                 = date($format, mktime(0, 0, 0, $m, ($de - $i), $y));
                    $arryTemp['date'][]    = __(date('d-M', strtotime($date)));
                    $arryTemp['dealcall'][] = DealCall::whereDate('created_at', $date)->where('user_id', $creatorId)->count();
                }
                $chartcall = $arryTemp;
                $chartcall['user']    = $arrCount['user'];
                $chartcall['deal']    = $arrCount['deal'];
            } elseif ($user->hasRole('client')) {
                $temp = [];
                for ($i = 0; $i <= 7 - 1; $i++) {
                    $date                 = date($format, mktime(0, 0, 0, $m, ($de - $i), $y));
                    $temp['date'][]    = __(date('d-M', strtotime($date)));
                    $temp['deal'][] = Deal::whereDate('created_at', $date)->where('created_by', $creatorId)->count();
                }
                $dealdata = $temp;
                $dealdata['user']    = User::where('type', '!   =', 'client')->where('created_by', $creatorId)->where('workspace_id', '=', $getActiveWorkSpace)->count();
                foreach ($usr->clientDeals as $deal) {
                    foreach ($deal->tasks as $task) {
                        $calenderTasks[] = [
                            'title' => $task->name,
                            'start' => $task->date,
                            'url' => route(
                                'deals.tasks.show',
                                [
                                    $deal->id,
                                    $task->id,
                                ]
                            ),
                            'className' => ($task->status) ? 'event-success border-success' : 'event-warning border-warning',
                        ];
                    }

                    $calenderTasks[] = [
                        'title' => $deal->name,
                        'start' => $deal->created_at->format('Y-m-d'),
                        'url' => route('deals.show', [$deal->id]),
                        'className' => 'deal event-primary border-primary',
                    ];
                }

                $client_deal         = $usr->clientDeals->pluck('id');
                $arrCount['deal']    = $usr->clientDeals->count();
                if (!empty($client_deal->first())) {
                    $arrCount['task'] = DealTask::whereIn('deal_id', $client_deal)->count();
                } else {
                    $arrCount['task'] = 0;
                }
            } else {
                $arrTemp = [];

                $chartData = $arrTemp;
                foreach ($usr->deals as $deal) {
                    foreach ($deal->tasks as $task) {
                        $calenderTasks[] = [
                            'title' => $task->name,
                            'start' => $task->date,
                            'url' => route(
                                'deals.tasks.show',
                                [
                                    $deal->id,
                                    $task->id,
                                ]
                            ),
                            'className' => ($task->status) ? 'event-success border-success' : 'event-warning border-warning',
                        ];
                    }

                    $calenderTasks[] = [
                        'title' => $deal->name,
                        'start' => $deal->created_at->format('Y-m-d'),
                        'url' => route('deals.show', [$deal->id]),
                        'className' => 'deal bg-primary border-primary',
                    ];
                }
                $user_deal           = $usr->deals->pluck('id');

                $arrCount['deal']    = $usr->deals()->count();
                if (!empty($user_deal->first())) {
                    $arrCount['task'] = DealTask::whereIn('deal_id', $user_deal)->count();
                } else {
                    $arrCount['task'] = 0;
                }
            }
            $user->save();
            if ($user->type == 'client') {
                $deals = Deal::select('deals.*')
                        ->join('client_deals', 'client_deals.deal_id', '=', 'deals.id')
                        ->where('client_deals.client_id', '=', $user->id)
                        ->where('deals.workspace_id', '=', getActiveWorkSpace())
                        ->orderBy('deals.created_at', 'desc')
                        ->take(5)->with('stage')->get();

                $modifiedDeals = Deal::select('deals.*')
                        ->join('client_deals', 'client_deals.deal_id', '=', 'deals.id')
                        ->where('client_deals.client_id', '=', $user->id)
                        ->where('deals.workspace_id', '=', getActiveWorkSpace())
                        ->orderBy('deals.updated_at', 'desc')
                        ->take(5)->with('stage')->get();
            } else {
                $deals = Deal::select('deals.*')
                        ->join('user_deals', 'user_deals.deal_id', '=', 'deals.id')
                        ->where('user_deals.user_id', '=', $user->id)
                        ->where('deals.workspace_id', '=', getActiveWorkSpace())
                        ->orderBy('deals.created_at', 'desc')
                        ->take(5)->with('stage')->get();

                $modifiedDeals = Deal::select('deals.*')
                        ->join('user_deals', 'user_deals.deal_id', '=', 'deals.id')
                        ->where('user_deals.user_id', '=', $user->id)
                        ->where('deals.workspace_id', '=', getActiveWorkSpace())
                        ->orderBy('deals.updated_at', 'desc')
                        ->take(5)->with('stage')->get();
            }

            $user = Auth::user()->name;
            $workspace       = WorkSpace::where('id', $getActiveWorkSpace)->first();

            $deal_stage = DealStage::where('created_by', $creatorId)->where('workspace_id', '=', $getActiveWorkSpace)->orderBy('order', 'ASC')->get();

            $dealStageName = [];
            $dealStageData = [];
            foreach ($deal_stage as $deal_stage_data) {
                $deal_stage = Deal::where('created_by', $creatorId)->where('workspace_id', '=', $getActiveWorkSpace)->where('stage_id', $deal_stage_data->id)->orderBy('order', 'ASC')->count();
                $dealStageName[] = $deal_stage_data->name;
                $dealStageData[] = $deal_stage;
            }

            return view('lead::index', compact('calenderTasks', 'transdate', 'arrErr', 'arrCount', 'chartData', 'chartcall', 'deals', 'dealdata', 'dealStageName', 'dealStageData', 'workspace', 'modifiedDeals'));
        } else {
            return redirect()->back()->with('error', 'permission Denied');
        }
    }

    public function index()
    {
        if (Auth::user()->isAbleTo('lead manage')) {

            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            if (Auth::user()->default_pipeline) {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->where('id', '=', Auth::user()->default_pipeline)->first();
                if (!$pipeline) {
                    $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                }
            } else {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
            }
            $pipelines = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');
            return view('lead::leads.index', compact('pipelines', 'pipeline'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('lead create')) {

            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            if (Auth::user()->type == "company") {
                $users = User::where('created_by', '=', $creatorId)->where('type', '!=', 'client')->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');
            } else {
                $users = User::where('id', '=', Auth::user()->id)->where('type', '!=', 'client')->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');
            }
            if (count($users) != 0) {

                $users->prepend(__('Select User'), '');
            }
            if (module_is_active('CustomField')) {
                $customFields =  \Workdo\CustomField\Entities\CustomField::where('workspace_id', $getActiveWorkSpace)->where('module', '=', 'lead')->where('sub_module', 'lead')->get();
            } else {
                $customFields = null;
            }

            return view('lead::leads.create', compact('users', 'customFields'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $usr = Auth::user();
        if ($usr->isAbleTo('lead create')) {

            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            $validator = \Validator::make(
                $request->all(),
                [
                    'subject'           => 'required|string|max:255',
                    'name'              => 'required|string|max:255',
                    'email'             => 'required|email|max:255',
                    'follow_up_date'    => 'nullable|date',
                    'phone'             => [
                        'required',
                        'regex:/^\+\d{1,3}\d{9,13}$/'
                    ],
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            // Default Field Value
            if ($usr->default_pipeline) {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->where('id', '=', $usr->default_pipeline)->first();
                if (!$pipeline) {
                    $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                }
            } else {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
            }
            if (!empty($pipeline)) {
                $stage = LeadStage::where('pipeline_id', '=', $pipeline->id)->where('workspace_id', $getActiveWorkSpace)->first();
            } else {
                return redirect()->back()->with('error', __('Please create pipeline.'));
            }
            if (empty($stage)) {
                return redirect()->back()->with('error', __('Please create stage for this pipeline.'));
            } else {
                $lead                 = new Lead();
                $lead->name           = $request->name;
                $lead->email          = $request->email;
                $lead->subject        = $request->subject;
                $lead->user_id        = $request->user_id;
                $lead->pipeline_id    = $pipeline->id;
                $lead->stage_id       = $stage->id;
                $lead->phone          = $request->phone;
                $lead->created_by     = $creatorId;
                $lead->workspace_id   = $getActiveWorkSpace;
                $lead->date           = date('Y-m-d');
                $lead->follow_up_date = $request->follow_up_date;
                $lead->save();

                if (module_is_active('CustomField')) {
                    \Workdo\CustomField\Entities\CustomField::saveData($lead, $request->customField);
                }

                if (Auth::user()->hasRole('company')) {
                    $usrLeads = [
                        $usr->id,
                        $request->user_id,
                    ];
                } else {
                    $usrLeads = [
                        $creatorId,
                        $request->user_id,
                    ];
                }

                foreach ($usrLeads as $usrLead) {
                    UserLead::create(
                        [
                            'user_id' => $usrLead,
                            'lead_id' => $lead->id,
                        ]
                    );
                }

                $leadArr = [
                    'lead_id' => $lead->id,
                    'name' => $lead->name,
                    'updated_by' => $usr->id,
                ];
                if (!empty(company_setting('Lead Assigned')) && company_setting('Lead Assigned')  == true) {
                    $lArr    = [
                        'lead_name' => $lead->name,
                        'lead_email' => $lead->email,
                        'lead_pipeline' => $pipeline->name,
                        'lead_stage' => $stage->name,
                    ];
                    $usrEmail = User::find($request->user_id);

                    // Send Email
                    $resp = EmailTemplate::sendEmailTemplate('Lead Assigned', [$usrEmail->id => $usrEmail->email], $lArr);
                }

                event(new CreateLead($request, $lead));

                $resp = null;
                $resp['is_success'] = true;
                return redirect()->back()->with('success', __('The lead has been created successfully.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(Lead $lead)
    {
        if (Auth::user()->isAbleTo('lead show')) {
            if ($lead->is_active) {

                $calenderTasks = [];
                $deal          = Deal::where('id', '=', $lead->is_converted)->first();
                $stageCnt      = LeadStage::where('pipeline_id', '=', $lead->pipeline_id)->where('created_by', '=', $lead->created_by)->get();
                $i             = 0;
                foreach ($stageCnt as $stage) {
                    $i++;
                    if ($stage->id == $lead->stage_id) {
                        break;
                    }
                }
                $precentage = number_format(($i * 100) / count($stageCnt));

                if (module_is_active('CustomField')) {
                    $lead->customField = \Workdo\CustomField\Entities\CustomField::getData($lead, 'lead', 'lead');
                    $customFields      = \Workdo\CustomField\Entities\CustomField::where('workspace_id', '=', getActiveWorkSpace())->where('module', '=', 'lead')->where('sub_module', 'lead')->get();
                } else {
                    $customFields = null;
                }

                return view('lead::leads.show', compact('lead', 'calenderTasks', 'deal', 'precentage', 'customFields'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Lead $lead)
    {
        if (Auth::user()->isAbleTo('lead edit')) {

            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            if ($lead->created_by == $creatorId) {
                $pipelines = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');
                $pipelines->prepend(__('Select Pipeline'), '');
                $sources = Source::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');
                if (module_is_active('ProductService')) {
                    $products = ProductService::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');
                }
                $users = User::where('created_by', '=', $creatorId)->where('type', '!=', 'client')->get()->pluck('name', 'id');
                $users->prepend(__('Select User'),null);

                $lead->sources  = explode(',', $lead->sources);
                $lead->products = explode(',', $lead->products);

                if (module_is_active('CustomField')) {
                    $lead->customField = \Workdo\CustomField\Entities\CustomField::getData($lead, 'lead', 'lead');
                    $customFields             = \Workdo\CustomField\Entities\CustomField::where('workspace_id', '=', $getActiveWorkSpace)->where('module', '=', 'lead')->where('sub_module', 'lead')->get();
                } else {
                    $customFields = null;
                }

                return view('lead::leads.edit', compact('lead', 'pipelines', 'sources', 'products', 'users', 'customFields'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, Lead $lead)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $creatorId          = creatorId();

            if ($lead->created_by == $creatorId) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'subject'       => 'required|string|max:255',
                        'name'          => 'required|string|max:255',
                        'email'         => 'required|email|max:255',
                        'pipeline_id'   => 'required|integer|exists:pipelines,id',
                        'user_id'       => 'required|integer|exists:users,id',
                        'stage_id'      => 'required|integer|exists:lead_stages,id',
                        'phone'         => [
                            'required',
                            'regex:/^\+\d{1,3}\d{9,13}$/'
                        ],
                        'sources'           => 'nullable|array',
                        'sources.*'         => 'integer|exists:sources,id',
                        'products'          => 'nullable|array',
                        'products.*'        => 'integer|exists:product_services,id',
                        'follow_up_date'    => 'nullable|date',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $lead->name           = $request->name;
                $lead->email          = $request->email;
                $lead->subject        = $request->subject;
                $lead->user_id        = $request->user_id;
                $lead->pipeline_id    = $request->pipeline_id;
                $lead->stage_id       = $request->stage_id;
                $lead->sources        = isset($request->sources) && !empty($request->sources) ? implode(",", array_filter($request->sources)) : null;
                $lead->products       = isset($request->products) && !empty($request->products) ? implode(",", array_filter($request->products)) : null;
                $lead->notes          = $request->notes;
                $lead->phone          = $request->phone;
                $lead->follow_up_date = $request->follow_up_date;
                $lead->save();


                if (module_is_active('CustomField')) {
                    \Workdo\CustomField\Entities\CustomField::saveData($lead, $request->customField);
                }
                event(new UpdateLead($request, $lead));

                return redirect()->back()->with('success', __('The lead deatails are updated successfully.'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Lead $lead)
    {
        if (Auth::user()->isAbleTo('lead delete')) {

            LeadDiscussion::where('lead_id', '=', $lead->id)->delete();
            UserLead::where('lead_id', '=', $lead->id)->delete();
            $leadfiles = LeadFile::where('lead_id', '=', $lead->id)->get();
            foreach ($leadfiles as $leadfile) {

                delete_file($leadfile->file_path);
                $leadfile->delete();
            }
            LeadActivityLog::where('lead_id', '=', $lead->id)->delete();
            if (module_is_active('CustomField')) {
                $customFields = \Workdo\CustomField\Entities\CustomField::where('module', 'lead')->where('sub_module', 'lead')->get();
                foreach ($customFields as $customField) {
                    $value = \Workdo\CustomField\Entities\CustomFieldValue::where('record_id', '=', $lead->id)->where('field_id', $customField->id)->first();
                    if (!empty($value)) {
                        $value->delete();
                    }
                }
            }
            event(new DestroyLead($lead));

            $lead->delete();

            return redirect()->back()->with('success', __('The lead has been deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function lead_list(LeadDataTable $dataTable)
    {
        $usr = Auth::user();

        if ($usr->isAbleTo('lead manage')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            if ($usr->default_pipeline) {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('id', '=', $usr->default_pipeline)->first();
                if (!$pipeline) {
                    $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                }
            } else {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
            }

            $pipelines = Pipeline::where('created_by', '=', $creatorId)->get()->pluck('name', 'id');
            return $dataTable->render('lead::leads.list', compact('pipelines', 'pipeline'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function json(Request $request)
    {
        $lead_stages = new LeadStage();
        if ($request->pipeline_id && !empty($request->pipeline_id)) {
            $lead_stages = $lead_stages->where('pipeline_id', '=', $request->pipeline_id);
            $lead_stages = $lead_stages->get()->pluck('name', 'id');
        } else {
            $lead_stages = [];
        }

        return response()->json($lead_stages);
    }

    public function fileUpload($id, Request $request)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $lead = Lead::find($id);
            $creatorId          = creatorId();

            if ($lead->created_by == $creatorId) {

                $file_name = $request->file->getClientOriginalName();
                $file_path = $request->lead_id . "_" . md5(time()) . "_" . $request->file->getClientOriginalName();

                $url = upload_file($request, 'file', $file_name, 'leads', []);
                if (isset($url['flag']) && $url['flag'] == 1) {
                    $file                 = LeadFile::create(
                        [
                            'lead_id' => $request->lead_id,
                            'file_name' => $file_name,
                            'file_path' => $url['url'],
                        ]
                    );
                    $return               = [];
                    $return['is_success'] = true;
                    $return['download']   =  get_file($url['url']);
                    $return['delete']     = route(
                        'leads.file.delete',
                        [
                            $lead->id,
                            $file->id,
                        ]
                    );

                    LeadActivityLog::create(
                        [
                            'user_id' => Auth::user()->id,
                            'lead_id' => $lead->id,
                            'log_type' => 'Upload File',
                            'remark' => json_encode(['file_name' => $file_name]),
                        ]
                    );

                    event(new LeadUploadFile($request, $lead));

                    return response()->json($return);
                } else {
                    return response()->json(
                        [
                            'is_success' => false,
                            'error' => $url['msg'],
                        ],
                        401
                    );
                }
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function fileDownload($id, $file_id)
    {
        if (Auth::user()->isAbleTo('lead show')) {
            $creatorId          = creatorId();

            $lead = Lead::find($id);
            if ($lead->created_by == $creatorId) {
                $file = LeadFile::find($file_id);
                if ($file) {
                    $file_path = get_base_file($file->file_path);
                    $filename  = $file->file_name;

                    return \Response::download(
                        $file_path,
                        $filename,
                        [
                            'Content-Length: ' . get_size($file_path),
                        ]
                    );
                } else {
                    return redirect()->back()->with('error', __('The file does not exist.'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function fileDelete($id, $file_id)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $lead = Lead::find($id);
            $creatorId          = creatorId();

            if ($lead->created_by == $creatorId) {
                $file = LeadFile::find($file_id);
                if ($file) {
                    delete_file($file->file_path);
                    $file->delete();

                    event(new DestroyLeadFile($lead));

                    return response()->json(['is_success' => true, 'success' => __('The file has been deleted.')], 200);
                } else {
                    return response()->json(
                        [
                            'is_success' => false,
                            'error' => __('The file does not exist.'),
                        ],
                        200
                    );
                }
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function noteStore($id, Request $request)
    {
        $lead = Lead::find($id);
        $creatorId          = creatorId();

        if ($lead->created_by == $creatorId) {
            $lead->notes = $request->notes;
            $lead->save();

            event(new LeadAddNote($request, $lead));

            return response()->json(
                [
                    'is_success' => true,
                    'success' => __('The note has been saved successfully.'),
                ],
                200
            );
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function labels($id)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $lead = Lead::find($id);
            $creatorId          = creatorId();

            if ($lead->created_by == $creatorId) {
                $labels   = Label::where('pipeline_id', '=', $lead->pipeline_id)->get();
                $selected = $lead->labels();
                if ($selected) {
                    $selected = $selected->pluck('name', 'id')->toArray();
                } else {
                    $selected = [];
                }

                return view('lead::leads.labels', compact('lead', 'labels', 'selected'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function labelStore($id, Request $request)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $leads = Lead::find($id);
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            if ($leads->created_by == $creatorId && $leads->workspace_id == $getActiveWorkSpace) {
                if ($request->labels) {
                    $leads->labels = implode(',', $request->labels);
                } else {
                    $leads->labels = $request->labels;
                }
                $leads->save();

                return redirect()->back()->with('success', __('The label details are updated successfully.'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function userEdit($id)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $lead = Lead::find($id);
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            if ($lead->created_by == $creatorId && $lead->workspace_id == $getActiveWorkSpace) {
                $users = User::where('active_workspace', '=', $getActiveWorkSpace)->where('created_by', '=', $creatorId)->where('type', '!=', 'client')->whereNOTIn(
                    'id',
                    function ($q) use ($lead) {
                        $q->select('user_id')->from('user_leads')->where('lead_id', '=', $lead->id);
                    }
                )->get();

                // foreach ($users as $key => $user) {
                //     if (!$user->isAbleTo('lead manage')) {
                //         $users->forget($key);
                //     }
                // }
                $users = $users->pluck('name', 'id');
                return view('lead::leads.users', compact('lead', 'users'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function userUpdate($id, Request $request)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $usr  = Auth::user();
            $lead = Lead::find($id);
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            if ($lead->created_by == $creatorId && $lead->workspace_id == $getActiveWorkSpace) {
                if (!empty($request->users)) {
                    $users   = array_filter($request->users);
                    $leadArr = [
                        'lead_id' => $lead->id,
                        'name' => $lead->name,
                        'updated_by' => $usr->id,
                    ];

                    foreach ($users as $user) {
                        UserLead::create(
                            [
                                'lead_id' => $lead->id,
                                'user_id' => $user,
                            ]
                        );
                    }
                }

                event(new LeadAddUser($request, $lead));

                if (!empty($users) && !empty($request->users)) {
                    return redirect()->back()->with('success', __('Users have been updated successfully.'));
                } else {
                    return redirect()->back()->with('error', __('Please select valid user.'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function userDestroy($id, $user_id)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $lead = Lead::find($id);
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            if ($lead->created_by == $creatorId && $lead->workspace_id == $getActiveWorkSpace) {
                UserLead::where('lead_id', '=', $lead->id)->where('user_id', '=', $user_id)->delete();

                event(new DestroyLeadUser($lead));

                return redirect()->back()->with('success', __('The user has been deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function  productEdit($id)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $lead = Lead::find($id);
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            if ($lead->created_by == creatorId()) {
                $products = [];
                if (module_is_active('ProductService')) {
                    $products = \Workdo\ProductService\Entities\ProductService::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->whereNOTIn('id', explode(',', $lead->products))->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');
                }
                return view('lead::leads.products', compact('lead', 'products'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function productUpdate($id, Request $request)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $usr        = Auth::user();
            $lead       = Lead::find($id);
            $lead_users = $lead->users->pluck('id')->toArray();
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();


            if ($lead->created_by == $creatorId && $lead->workspace_id == $getActiveWorkSpace) {
                if (!empty($request->products)) {
                    $products       = array_filter($request->products);
                    $old_products   = explode(',', $lead->products);
                    $lead->products = implode(',', array_merge($old_products, $products));
                    $lead->save();

                    $objProduct = ProductService::whereIN('id', $products)->get()->pluck('name', 'id')->toArray();

                    LeadActivityLog::create(
                        [
                            'user_id' => $usr->id,
                            'lead_id' => $lead->id,
                            'log_type' => 'Add Product',
                            'remark' => json_encode(['title' => implode(",", $objProduct)]),
                        ]
                    );

                    $productArr = [
                        'lead_id' => $lead->id,
                        'name' => $lead->name,
                        'updated_by' => $usr->id,
                    ];
                }

                event(new LeadAddProduct($request, $lead));

                if (!empty($products) && !empty($request->products)) {
                    return redirect()->back()->with('success', __('Products have been updated successfully.'))->with('status', 'products');
                } else {
                    return redirect()->back()->with('error', __('Please select valid product.'))->with('status', 'general');
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'products');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'products');
        }
    }

    public function productDestroy($id, $product_id)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $lead = Lead::find($id);
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            if ($lead->created_by == $creatorId && $lead->workspace_id == $getActiveWorkSpace) {
                $products = explode(',', $lead->products);
                foreach ($products as $key => $product) {
                    if ($product_id == $product) {
                        unset($products[$key]);
                    }
                }
                $lead->products = implode(',', $products);
                $lead->save();

                event(new DestroyLeadProduct($lead));

                return redirect()->back()->with('success', __('The product has been deleted.'))->with('status', 'products');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'products');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'products');
        }
    }
    public function sourceEdit($id)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $lead = Lead::find($id);
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            if ($lead->created_by == $creatorId && $lead->workspace_id == $getActiveWorkSpace) {
                $sources = Source::where('created_by', '=', $creatorId)->where('workspace_id', '=', $getActiveWorkSpace)->get();

                $selected = $lead->sources();
                if ($selected) {
                    $selected = $selected->pluck('name', 'id')->toArray();
                }

                return view('lead::leads.sources', compact('lead', 'sources', 'selected'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function sourceUpdate($id, Request $request)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $usr        = Auth::user();
            $lead       = Lead::find($id);
            $lead_users = $lead->users->pluck('id')->toArray();
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            if ($lead->created_by == $creatorId && $lead->workspace_id == $getActiveWorkSpace) {
                if (!empty($request->sources) && count($request->sources) > 0) {
                    $lead->sources = implode(',', $request->sources);
                } else {
                    $lead->sources = "";
                }

                $lead->save();

                LeadActivityLog::create(
                    [
                        'user_id' => $usr->id,
                        'lead_id' => $lead->id,
                        'log_type' => 'Update Sources',
                        'remark' => json_encode(['title' => 'Update Sources']),
                    ]
                );

                $leadArr = [
                    'lead_id' => $lead->id,
                    'name' => $lead->name,
                    'updated_by' => $usr->id,
                ];

                event(new LeadSourceUpdate($request, $lead));

                return redirect()->back()->with('success', __('The sources has been changes successfully'))->with('status', 'sources');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'sources');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'sources');
        }
    }

    public function sourceDestroy($id, $source_id)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $lead = Lead::find($id);
            if ($lead->created_by == creatorId() && $lead->workspace_id == getActiveWorkSpace()) {
                $sources = explode(',', $lead->sources);
                foreach ($sources as $key => $source) {
                    if ($source_id == $source) {
                        unset($sources[$key]);
                    }
                }
                $lead->sources = implode(',', $sources);
                $lead->save();

                event(new DestroyLeadSource($lead));

                return redirect()->back()->with('success', __('The source has been deleted.'))->with('status', 'sources');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'sources');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'sources');
        }
    }

    public function discussionCreate($id)
    {
        $lead = Lead::find($id);
        if ($lead->created_by == creatorId() && $lead->workspace_id == getActiveWorkSpace()) {
            return view('lead::leads.discussions', compact('lead'));
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function discussionStore($id, Request $request)
    {
        $usr        = Auth::user();
        $lead       = Lead::find($id);
        $lead_users = $lead->users->pluck('id')->toArray();

        if ($lead->created_by == creatorId()) {
            $discussion             = new LeadDiscussion();
            $discussion->comment    = $request->comment;
            $discussion->lead_id    = $lead->id;
            $discussion->created_by = $usr->id;
            $discussion->save();

            $leadArr = [
                'lead_id' => $lead->id,
                'name' => $lead->name,
                'updated_by' => $usr->id,
            ];

            event(new LeadAddDiscussion($request, $lead));

            return redirect()->back()->with('success', __('The message has been added successfully.'))->with('status', 'discussion');
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'discussion');
        }
    }

    public function order(Request $request)
    {
        try {
            if (Auth::user()->isAbleTo('lead move')) {
                $usr        = Auth::user();
                $post       = $request->all();
                $lead       = Lead::find($post['lead_id']);
                $lead_users = $lead->users->pluck('email', 'id')->toArray();

                if ($lead->stage_id != $post['stage_id']) {

                    $newStage = LeadStage::find($post['stage_id']);

                    LeadActivityLog::create(
                        [
                            'user_id' => Auth::user()->id,
                            'lead_id' => $lead->id,
                            'log_type' => 'Move',
                            'remark' => json_encode(
                                [
                                    'title' => $lead->name,
                                    'old_status' => $lead->stage->name,
                                    'new_status' => $newStage->name,
                                ]
                            ),
                        ]
                    );

                    $leadArr = [
                        'lead_id' => $lead->id,
                        'name' => $lead->name,
                        'updated_by' => $usr->id,
                        'old_status' => $lead->stage->name,
                        'new_status' => $newStage->name,
                    ];


                    if (!empty(company_setting('Lead Moved')) && company_setting('Lead Moved')  == true) {

                        $lArr = [
                            'lead_name' => $lead->name,
                            'lead_email' => $lead->email,
                            'lead_pipeline' => $lead->pipeline->name,
                            'lead_stage' => $lead->stage->name,
                            'lead_old_stage' => $lead->stage->name,
                            'lead_new_stage' => $newStage->name,
                        ];

                        // Send Email
                        EmailTemplate::sendEmailTemplate('Lead Moved', $lead_users, $lArr);
                    }
                }
                event(new LeadMoved($request, $lead));

                foreach ($post['order'] as $key => $item) {

                    $leads = Lead::where('id', $item)->update(['order' => $key, 'stage_id' => $post['stage_id']]);
                }
                return response()->json(['success' => __('Lead moved successfully.')]);
            } else {
                return response()->json(['error' => __('Permission denied.')]);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => __('Something went wrong.')]);
        }
    }

    public function showConvertToDeal($id)
    {
        $creatorId          = creatorId();

        $lead         = Lead::findOrFail($id);
        $exist_client = User::where('type', '=', 'client')->where('email', '=', $lead->email)->where('created_by', '=', $creatorId)->first();
        $clients      = User::where('type', '=', 'client')->where('created_by', '=', $creatorId)->get();

        return view('lead::leads.convert', compact('lead', 'exist_client', 'clients'));
    }

    public function convertToDeal($id, Request $request)
    {
        $lead = Lead::findOrFail($id);
        $usr  = Auth::user();
        $creatorId          = creatorId();
        $getActiveWorkSpace = getActiveWorkSpace();

        if ($request->client_check == 'exist') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'clients'   => 'required|email|exists:users,email',
                    'price'     => 'numeric|min:0',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $client = User::where('type', '=', 'client')->where('email', '=', $request->clients)->where('created_by', '=', $creatorId)->first();

            if (empty($client)) {
                return redirect()->back()->with('error', 'The client is not available now.');
            }
        } else {
            $validator = \Validator::make(
                $request->all(),
                [
                    'client_name'       => 'required|string|max:255',
                    'client_email'      => 'required|email|unique:users,email',
                    'client_password'   => 'required',
                    'price'             => 'min:0',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $role   = Role::where('name', 'client')->where('created_by', '=', $creatorId)->first();
            $client = User::create(
                [
                    'name' => $request->client_name,
                    'email' => $request->client_email,
                    'password' => \Hash::make($request->client_password),
                    'email_verified_at' => date('Y-m-d h:i:s'),
                    'type' => 'client',
                    'lang' => 'en',
                    'created_by' => $creatorId,
                    'workspace_id' => $getActiveWorkSpace,
                    'active_workspace' => $getActiveWorkSpace,
                ]
            );
            $client->addRole($role);

            $cArr = [
                'email' => $request->client_email,
                'password' => $request->client_password,
            ];

            // Send Email to client if they are new created.
            EmailTemplate::sendEmailTemplate('New User', [$client->id => $client->email], $cArr);
        }

        // Create Deal
        $stage = DealStage::where('pipeline_id', '=', $lead->pipeline_id)->first();
        if (empty($stage)) {
            return redirect()->back()->with('error', __('Please create stage for this pipeline.'));
        }

        $deal              = new Deal();
        $deal->name        = $request->name;
        $deal->price       = empty($request->price) ? 0 : $request->price;
        $deal->pipeline_id = $lead->pipeline_id;
        $deal->stage_id    = $stage->id;
        $deal->sources     = in_array('sources', $request->is_transfer) ? $lead->sources : '';
        $deal->products    = in_array('products', $request->is_transfer) ? $lead->products : '';
        $deal->notes       = in_array('notes', $request->is_transfer) ? $lead->notes : '';
        $deal->labels      = $lead->labels;
        $deal->status      = 'Active';
        $deal->workspace_id  = $getActiveWorkSpace;
        $deal->created_by  = $lead->created_by;
        $deal->save();
        // end create deal

        // Make entry in ClientDeal Table
        ClientDeal::create(
            [
                'deal_id' => $deal->id,
                'client_id' => $client->id,
            ]
        );

        $leadTasks = LeadTask::where('lead_id', '=', $lead->id)->get();

        foreach ($leadTasks as $leadTask) {
            DealTask::create(
                [
                    'deal_id' => $deal->id,
                    'name' => $leadTask->name,
                    'date' => $leadTask->date,
                    'time' => $leadTask->time,
                    'priority' => $leadTask->priority,
                    'status' => $leadTask->status,
                    'workspace' => $leadTask->workspace,
                ]
            );
        }

        // end

        if (!empty(company_setting('Deal Assigned')) && company_setting('Deal Assigned')  == true) {
            $dealArr = [
                'deal_id' => $deal->id,
                'name' => $deal->name,
                'updated_by' => $usr->id,
            ];

            // Send Mail
            $pipeline = Pipeline::find($lead->pipeline_id);
            $dArr     = [
                'deal_name' => $deal->name,
                'deal_pipeline' => $pipeline->name,
                'deal_stage' => $stage->name,
                'deal_status' => $deal->status,
                'deal_price' => currency_format_with_sym($deal->price),
            ];
            EmailTemplate::sendEmailTemplate('Deal Assigned', [$client->id => $client->email], $dArr);
        }
        // Make Entry in UserDeal Table
        $leadUsers = UserLead::where('lead_id', '=', $lead->id)->get();
        foreach ($leadUsers as $leadUser) {
            UserDeal::create(
                [
                    'user_id' => $leadUser->user_id,
                    'deal_id' => $deal->id,
                ]
            );
        }
        // end

        //Transfer Lead Discussion to Deal
        if (in_array('discussion', $request->is_transfer)) {
            $discussions = LeadDiscussion::where('lead_id', '=', $lead->id)->where('created_by', '=', $creatorId)->get();
            if (!empty($discussions)) {
                foreach ($discussions as $discussion) {
                    DealDiscussion::create(
                        [
                            'deal_id' => $deal->id,
                            'comment' => $discussion->comment,
                            'created_by' => $discussion->created_by,
                        ]
                    );
                }
            }
        }
        // end Transfer Discussion

        // Transfer Lead Files to Deal
        if (in_array('files', $request->is_transfer)) {
            $files = LeadFile::where('lead_id', '=', $lead->id)->get();
            if (!empty($files)) {
                foreach ($files as $file) {
                    $location     = base_path() . '/' . $file->file_path;
                    $new_location = base_path() . '/' . $file->file_path;
                    $copied       = copy($location, $new_location);

                    if ($copied) {
                        DealFile::create(
                            [
                                'deal_id' => $deal->id,
                                'file_name' => $file->file_name,
                                'file_path' => $file->file_path,
                            ]
                        );
                    }
                }
            }
        }
        // end Transfer Files

        // Transfer Lead Calls to Deal
        if (in_array('calls', $request->is_transfer)) {
            $calls = LeadCall::where('lead_id', '=', $lead->id)->get();
            if (!empty($calls)) {
                foreach ($calls as $call) {
                    DealCall::create(
                        [
                            'deal_id' => $deal->id,
                            'subject' => $call->subject,
                            'call_type' => $call->call_type,
                            'duration' => $call->duration,
                            'user_id' => $call->user_id,
                            'description' => $call->description,
                            'call_result' => $call->call_result,
                        ]
                    );
                }
            }
        }
        //end

        // Transfer Lead Emails to Deal
        if (in_array('emails', $request->is_transfer)) {
            $emails = LeadEmail::where('lead_id', '=', $lead->id)->get();
            if (!empty($emails)) {
                foreach ($emails as $email) {
                    DealEmail::create(
                        [
                            'deal_id' => $deal->id,
                            'to' => $email->to,
                            'subject' => $email->subject,
                            'description' => $email->description,
                        ]
                    );
                }
            }
        }

        // Update is_converted field as deal_id
        $lead->is_converted = $deal->id;
        $lead->save();

        event(new LeadConvertDeal($request, $lead));

        return redirect()->back()->with('success', __('The lead has been converted into a deal successfully.'));
    }

    // Lead Calls
    public function callCreate($id)
    {
        if (Auth::user()->isAbleTo('lead call create')) {
            $creatorId          = creatorId();
            $lead = Lead::find($id);
            if ($lead->created_by == $creatorId) {
                $users = UserLead::where('lead_id', '=', $lead->id)->get();

                return view('lead::leads.calls', compact('lead', 'users'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function callStore($id, Request $request)
    {
        if (Auth::user()->isAbleTo('lead call create')) {
            $usr  = Auth::user();
            $lead = Lead::find($id);
            if ($lead->created_by == creatorId() && $lead->workspace_id == getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'subject'       => 'required|string|max:255',
                        'call_type'     => 'required|in:outbound,inbound',
                        'user_id'       => 'required|integer|exists:users,id',
                        'duration'      => [
                            'required',
                            'regex:/^\d{2}:\d{2}:\d{2}$/',
                        ],
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $leadCall = LeadCall::create(
                    [
                        'lead_id' => $lead->id,
                        'subject' => $request->subject,
                        'call_type' => $request->call_type,
                        'duration' => $request->duration,
                        'user_id' => $request->user_id,
                        'description' => $request->description,
                        'call_result' => $request->call_result,
                    ]
                );

                LeadActivityLog::create(
                    [
                        'user_id' => $usr->id,
                        'lead_id' => $lead->id,
                        'log_type' => 'Create Lead Call',
                        'remark' => json_encode(['title' => 'Create new Lead Call']),
                    ]
                );

                $leadArr = [
                    'lead_id' => $lead->id,
                    'name' => $lead->name,
                    'updated_by' => $usr->id,
                ];

                event(new LeadAddCall($request, $lead));

                return redirect()->back()->with('success', __('The call has been created successfully.'))->with('status', 'calls');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
        }
    }

    public function callEdit($id, $call_id)
    {
        if (Auth::user()->isAbleTo('lead call edit')) {
            $lead = Lead::find($id);
            if ($lead->created_by == creatorId() && $lead->workspace_id == getActiveWorkSpace()) {
                $call  = LeadCall::find($call_id);
                $users = UserLead::where('lead_id', '=', $lead->id)->get();

                return view('lead::leads.calls', compact('call', 'lead', 'users'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function callUpdate($id, $call_id, Request $request)
    {
        if (Auth::user()->isAbleTo('lead call edit')) {
            $lead = Lead::find($id);
            if ($lead->created_by == creatorId() && $lead->workspace_id == getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'subject'       => 'required|string|max:255',
                        'call_type'     => 'required|in:outbound,inbound',
                        'user_id'       => 'required|integer|exists:users,id',
                        'duration'      => [
                            'required',
                            'regex:/^\d{2}:\d{2}:\d{2}$/',
                        ],
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $call = LeadCall::find($call_id);

                $call->update(
                    [
                        'subject' => $request->subject,
                        'call_type' => $request->call_type,
                        'duration' => $request->duration,
                        'user_id' => $request->user_id,
                        'description' => $request->description,
                        'call_result' => $request->call_result,
                    ]
                );

                event(new LeadUpdateCall($request, $lead));

                return redirect()->back()->with('success', __('The call details are updated successfully.'))->with('status', 'calls');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
        }
    }

    public function callDestroy($id, $call_id)
    {
        if (Auth::user()->isAbleTo('lead call delete')) {
            $lead = Lead::find($id);
            if ($lead->created_by == creatorId() && $lead->workspace_id == getActiveWorkSpace()) {
                $task = LeadCall::find($call_id);
                $task->delete();

                event(new DestroyLeadCall($lead));

                return redirect()->back()->with('success', __('The call has been deleted.'))->with('status', 'calls');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
        }
    }

    // Lead email
    public function emailCreate($id)
    {
        if (Auth::user()->isAbleTo('lead email create')) {
            $lead = Lead::find($id);
            if ($lead->created_by == creatorId() && $lead->workspace_id == getActiveWorkSpace()) {
                return view('lead::leads.emails', compact('lead'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function emailStore($id, Request $request)
    {
        if (Auth::user()->isAbleTo('lead email create')) {
            $lead = Lead::find($id);
            if ($lead->created_by == creatorId() && $lead->workspace_id == getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'to'        => 'required|email|max:255',
                        'subject'   => 'required|string|max:255',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $leadEmail = LeadEmail::create(
                    [
                        'lead_id' => $lead->id,
                        'to' => $request->to,
                        'subject' => $request->subject,
                        'description' => $request->description,
                    ]
                );

                LeadActivityLog::create(
                    [
                        'user_id' => Auth::user()->id,
                        'lead_id' => $lead->id,
                        'log_type' => 'Create Lead Email',
                        'remark' => json_encode(['title' => 'Create new Deal Email']),
                    ]
                );

                event(new LeadAddEmail($request, $lead));

                if (!empty(company_setting('Lead Emails')) && company_setting('Lead Emails')  == true) {
                    $lead_users[] = $request->to;
                    $lArr = [
                        'lead_name' => $lead->name,
                        'lead_email_subject' => $request->subject,
                        'lead_email_description' => $request->description,
                    ];

                    // Send Email
                   $resp = EmailTemplate::sendEmailTemplate('Lead Emails', $lead_users, $lArr);
                }

                return redirect()->back()->with('success', __('The email has been created successfully.') .((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'emails');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'emails');
        }
    }

    public function fileImportExport()
    {
        if (Auth::user()->isAbleTo('lead import')) {
            $user               = Auth::user();
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            if ($user->default_pipeline) {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->where('id', '=', $user->default_pipeline)->first();
                if (!$pipeline) {
                    $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                }
            } else {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
            }
            if (!empty($pipeline)) {
                $stage = LeadStage::where('pipeline_id', '=', $pipeline->id)->where('workspace_id', $getActiveWorkSpace)->first();
                if (empty($stage)) {
                    return response()->json(['error' => __('Please create stage for this pipeline.')], 401);
                }
            } else {
                return response()->json(['error' => __('Please create pipeline.')], 401);
            }
            return view('lead::leads.import');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function fileImport(Request $request)
    {
        if (Auth::user()->isAbleTo('lead import')) {
            session_start();

            $error = '';

            $html = '';

            if ($request->file->getClientOriginalName() != '') {
                $file_array = explode(".", $request->file->getClientOriginalName());

                $extension = end($file_array);
                if ($extension == 'csv') {
                    $file_data = fopen($request->file->getRealPath(), 'r');

                    $file_header = fgetcsv($file_data);
                    $html .= '<table class="table table-bordered"><tr>';

                    for ($count = 0; $count < count($file_header); $count++) {
                        $html .= '
                                <th>
                                    <select name="set_column_data" class="form-control set_column_data" data-column_number="' . $count . '">
                                        <option value="">Set Count Data</option>
                                        <option value="subject">Subject</option>
                                        <option value="name">Name</option>
                                        <option value="email">Email</option>
                                        <option value="phone">Phone No</option>
                                    </select>
                                </th>
                                ';
                    }

                    $html .= '
                                <th>
                                        <select name="set_column_data" class="form-control set_column_data user-name" data-column_number="' . $count + 1 . '">
                                            <option value="user">User</option>
                                        </select>
                                </th>
                                ';

                    $html .= '</tr>';
                    $limit = 0;
                    while (($row = fgetcsv($file_data)) !== false) {
                        $limit++;

                        $html .= '<tr>';

                        for ($count = 0; $count < count($row); $count++) {
                            $html .= '<td>' . $row[$count] . '</td>';
                        }

                        $html .= '<td>
                                    <select name="user" class="form-control user-name-value">;';
                        if (Auth::user()->type == "company") {
                            $users = User::where('created_by', '=', creatorId())->where('type', '!=', 'client')->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
                        } else {
                            $users = User::where('id', '=', Auth::user()->id)->where('type', '!=', 'client')->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
                        }
                        foreach ($users as $key => $user) {
                            $html .= ' <option value="' . $key . '">' . $user . '</option>';
                        }
                        $html .= '  </select>
                                </td>';

                        $html .= '</tr>';

                        $temp_data[] = $row;
                    }
                    $_SESSION['file_data'] = $temp_data;
                } else {
                    $error = 'Only <b>.csv</b> file allowed';
                }
            } else {

                $error = __('Please select CSV file');
            }
            $output = array(
                'error' => $error,
                'output' => $html,
            );

            return json_encode($output);
        } else {
            return redirect()->back()->with('error', __('permission Denied'));
        }
    }

    public function fileImportModal()
    {
        if (Auth::user()->isAbleTo('lead import')) {
            return view('lead::leads.import_modal');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function leadImportdata(Request $request)
    {
        if (Auth::user()->isAbleTo('lead import')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            session_start();
            $html = '<h3 class="text-danger text-center">Below data is not inserted</h3></br>';
            $flag = 0;
            $html .= '<table class="table table-bordered"><tr>';
            $file_data = $_SESSION['file_data'];
            foreach ($file_data as $validationKey => $value) {
                $validator = \Validator::make([
                    'subject' => $value[$request->subject] ?? null,
                    'name'    => $value[$request->name] ?? null,
                    'email'   => $value[$request->email] ?? null,
                    'phone'   => $value[$request->phone] ?? null,
                ], [
                    'subject' => 'required|string|max:255',
                    'name'    => 'required|string|max:255',
                    'email'   => 'required|email|max:255',
                    'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => $validator->errors()->first(),
                    ]);
                }
            }

            unset($_SESSION['file_data']);

            $user = Auth::user();
            if ($user->default_pipeline) {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->where('id', '=', $user->default_pipeline)->first();
                if (!$pipeline) {
                    $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                }
            } else {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
            }
            if (!empty($pipeline)) {
                $stage = LeadStage::where('pipeline_id', '=', $pipeline->id)->where('workspace_id', $getActiveWorkSpace)->first();
                if (empty($stage)) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Please create stage for this pipeline.'),
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => __('Please create pipeline.'),
                ]);
            }
            foreach ($file_data as $key => $row) {
                $leads = Lead::where('created_by', $creatorId)->where('workspace_id', $getActiveWorkSpace)->Where('email', 'like', $row[$request->email])->get();

                if ($leads->isEmpty()) {
                    try {

                        $users = User::find($request->user[$key]);
                        if (empty($users)) {
                            $users = User::where('created_by', Auth::user()->id)->first();
                        }
                        $lead = Lead::create([
                            'subject' => $row[$request->subject],
                            'name' => $row[$request->name],
                            'user_id' => $users->id,
                            'email' => $row[$request->email],
                            'phone' => $row[$request->phone],
                            'pipeline_id' => $pipeline->id,
                            'stage_id' => $stage->id,
                            'created_by' => $creatorId,
                            'workspace_id' => $getActiveWorkSpace,
                        ]);
                        UserLead::create([
                            'user_id' => $creatorId,
                            'lead_id' => $lead->id,
                        ]);
                    } catch (\Exception $e) {
                        $flag = 1;
                        $html .= '<tr>';

                        $html .= '<td>' . $row[$request->subject] . '</td>';
                        $html .= '<td>' . $row[$request->name] . '</td>';
                        $html .= '<td>' . $row[$request->email] . '</td>';
                        $html .= '<td>' . $row[$request->phone] . '</td>';

                        $html .= '</tr>';
                    }
                } else {
                    $flag = 1;
                    $html .= '<tr>';

                    $html .= '<td>' . $row[$request->subject] . '</td>';
                    $html .= '<td>' . $row[$request->name] . '</td>';
                    $html .= '<td>' . $row[$request->email] . '</td>';
                    $html .= '<td>' . $row[$request->phone] . '</td>';

                    $html .= '</tr>';
                }
            }

            $html .= '
                            </table>
                            <br />
                            ';
            if ($flag == 1) {

                return response()->json([
                    'html' => true,
                    'response' => $html,
                ]);
            } else {
                return response()->json([
                    'html' => false,
                    'response' => __('Data has been imported.'),
                ]);
            }
        } else {
            return redirect()->back()->with('error', 'permission Denied');
        }
    }

    public function taskCreate($id)
    {
        if (Auth::user()->isAbleTo('deal task create')) {
            $lead = Lead::find($id);
            if ($lead->created_by == creatorId() && $lead->workspace  = getActiveWorkSpace()) {
                $priorities = LeadTask::$priorities;
                $status     = LeadTask::$status;
                return view('lead::leads.tasks', compact('lead', 'priorities', 'status'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function taskStore($id, Request $request)
    {
        $usr = Auth::user();
        if ($usr->isAbleTo('lead task create')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $lead       = Lead::find($id);
            $lead_users = $lead->users->pluck('id')->toArray();
            $usrs       = User::whereIN('id', $lead_users)->get()->pluck('email', 'id')->toArray();

            if ($lead->created_by == $creatorId && $lead->workspace_id == $getActiveWorkSpace) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name'      => 'required|string|max:255',
                        'date'      => 'required|date',
                        'time'      => 'required|date_format:H:i',
                        'priority'  => 'required|in:1,2,3',
                        'status'    => 'required|in:0,1',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $leadTask = LeadTask::create(
                    [
                        'lead_id' => $lead->id,
                        'name' => $request->name,
                        'date' => $request->date,
                        'time' => date('H:i:s', strtotime($request->date . ' ' . $request->time)),
                        'priority' => $request->priority,
                        'status' => $request->status,
                        'workspace' => $getActiveWorkSpace,
                    ]
                );

                LeadActivityLog::create(
                    [
                        'user_id' => $usr->id,
                        'lead_id' => $lead->id,
                        'log_type' => 'Create Task',
                        'remark' => json_encode(['title' => $leadTask->name]),
                    ]
                );

                $taskArr = [
                    'lead_id' => $lead->id,
                    'name' => $lead->name,
                    'updated_by' => $usr->id,
                ];
                if (!empty(company_setting('New Task')) && company_setting('New Task')  == true) {
                    $tArr = [
                        'lead_name' => $lead->name,
                        'lead_pipeline' => $lead->pipeline->name,
                        'lead_stage' => $lead->stage->name,
                        'lead_status' => $lead->status,
                        'lead_price' => currency_format_with_sym($lead->price),
                        'task_name' => $leadTask->name,
                        'task_priority' => LeadTask::$priorities[$leadTask->priority],
                        'task_status' => LeadTask::$status[$leadTask->status],
                    ];

                    // Send Email
                    $resp = EmailTemplate::sendEmailTemplate('New Task', $usrs, $tArr);
                }

                event(new CreateLeadTask($request, $leadTask, $lead));
                return redirect()->back()->with('success', __('The task has been created successfully.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
        }
    }

    public function taskEdit($id, $task_id)
    {
        if (Auth::user()->isAbleTo('lead task edit')) {
            $lead = Lead::find($id);
            if ($lead->created_by == creatorId() && $lead->workspace_id == getActiveWorkSpace()) {
                $priorities = LeadTask::$priorities;
                $status     = LeadTask::$status;
                $task       = LeadTask::find($task_id);

                return view('lead::leads.tasks', compact('task', 'lead', 'priorities', 'status'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function taskUpdate($id, $task_id, Request $request)
    {
        if (Auth::user()->isAbleTo('lead task edit')) {
            $lead = Lead::find($id);
            if ($lead->created_by == creatorId() && $lead->workspace_id == getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name'      => 'required|string|max:255',
                        'date'      => 'required|date',
                        'time'      => 'required',
                        'priority'  => 'required|in:1,2,3',
                        'status'    => 'required|in:0,1',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $task = LeadTask::find($task_id);

                $task->update(
                    [
                        'name' => $request->name,
                        'date' => $request->date,
                        'time' => date('H:i:s', strtotime($request->date . ' ' . $request->time)),
                        'priority' => $request->priority,
                        'status' => $request->status,
                    ]
                );

                event(new UpdateLeadTask($request, $lead, $task));

                return redirect()->back()->with('success', __('The task details are updated successfully.'))->with('status', 'tasks');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
        }
    }

    public function taskUpdateStatus($id, $task_id, Request $request)
    {
        if (Auth::user()->isAbleTo('lead task edit')) {
            $lead = Lead::find($id);
            if ($lead->created_by == creatorId() && $lead->workspace_id == getActiveWorkSpace()) {

                $validator = \Validator::make(
                    $request->all(),
                    [
                        'status' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return response()->json(
                        [
                            'is_success' => false,
                            'error' => $messages->first(),
                        ],
                        401
                    );
                }

                $task = LeadTask::find($task_id);
                if ($request->status) {
                    $task->status = 0;
                } else {
                    $task->status = 1;
                }
                $task->save();

                event(new StatusChangeLeadTask($request, $lead, $task));

                return response()->json(
                    [
                        'is_success' => true,
                        'success' => __('The task status has been changes successfully.'),
                        'status' => $task->status,
                        'status_label' => __(LeadTask::$status[$task->status]),
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function taskDestroy($id, $task_id)
    {
        if (Auth::user()->isAbleTo('lead task delete')) {
            $lead = Lead::find($id);
            if ($lead->created_by == creatorId() && $lead->workspace_id == getActiveWorkSpace()) {
                $task = LeadTask::find($task_id);
                $task->delete();

                event(new DestroyLeadTask($lead));

                return redirect()->back()->with('success', __('The task has been deleted.'))->with('status', 'tasks');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
        }
    }
}
