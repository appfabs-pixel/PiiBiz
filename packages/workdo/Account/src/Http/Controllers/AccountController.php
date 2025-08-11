<?php

namespace Workdo\Account\Http\Controllers;

use App\Models\Setting;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Workdo\Account\Entities\AccountUtility;
use App\Models\Invoice;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function __construct()
    {
        if(module_is_active('GoogleAuthentication'))
        {
            $this->middleware('2fa');
        }
    }

    public function index()
    {
        if(Auth::check())
        {
            if (Auth::user()->isAbleTo('account dashboard manage'))
            {
                // Placeholder data for cards
                $data['totalPurchases']   = '300 kg';
                $data['totalProduction']  = '200 kg';
                $data['totalSales']       = '180 kg';
                $data['totalWastage']     = '10 kg';
                $data['Reusable']         = '10 kg';

                // <-- Add totalStock here to satisfy the view -->
                $data['totalStock'] = '120 kg'; // <-- placeholder, replace with real calculation below if desired

                /*
                 * If you have a Stock/Inventory model and want a real total, you can do something like:
                 *
                 * use App\Models\Stock; // add at top if available
                 * $quantitySum = Stock::sum('quantity'); // or appropriate column name
                 * $data['totalStock'] = $quantitySum . ' kg';
                 *
                 * Or compute as purchases - sales - wastage + reused depending on your data model:
                 * $data['totalStock'] = ($purchasesQty - $salesQty - $wastageQty + $reusedQty) . ' kg';
                 */

                // Placeholder data for graphs
                $data['labels'] = ['January', 'February', 'March', 'April', 'May', 'June', 'July'];
                $data['purchasesData'] = [65, 59, 80, 81, 56, 55, 40];
                $data['productionData'] = [28, 48, 40, 19, 86, 27, 90];
                $data['salesData'] = [45, 25, 55, 61, 46, 65, 50];
                $data['wastageData'] = [12, 19, 3, 5, 2, 3, 7];

                return view('account::dashboard.dashboard', $data);
            }
            else
            {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        }
        else
        {
            return redirect()->route('login');
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('account::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return redirect()->back();
        return view('account::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('account::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function setting(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'customer_prefix' => 'required',
            'vendor_prefix' => 'required',
        ]);
        if($validator->fails()){
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        else
        {
            $getActiveWorkSpace = getActiveWorkSpace();
            $creatorId = creatorId();
            $post = $request->all();
            unset($post['_token']);
            foreach ($post as $key => $value) {
                // Define the data to be updated or inserted
                $data = [
                    'key' => $key,
                    'workspace' => $getActiveWorkSpace,
                    'created_by' => $creatorId,
                ];

                // Check if the record exists, and update or insert accordingly
                Setting::updateOrInsert($data, ['value' => $value]);
            }
            // Settings Cache forget
            comapnySettingCacheForget();
            return redirect()->back()->with('success','Account setting save sucessfully.');
        }
    }
}
