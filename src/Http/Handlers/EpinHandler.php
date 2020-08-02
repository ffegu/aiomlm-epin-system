<?php

namespace Aiomlm\Epin\Http\Handlers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Aiomlm\Epin\Models\Epin;
use App\Models\User;
/**
 * use for epin management
 */
class EpinHandler
{

      /**
       * EpinHander only provide/manage epin data, it doest not check for any security, so don't forgot to
       * check security before passing here
       * @param  arrey $data - for pagination, status , search and others queries
       * @return response datatable
       */
     public static function epins($data)
     {
         if (auth()->user()->can('delete-epins')) {
           $epins = Epin::where('status', $data['status'])->paginate($data['paginate']);

           $search = $data['search'];

           if ($data['search'] || $data['search'] !=null) {
               $epins = Epin::where('status', $data['status'])
                              ->where('epin', 'like', "%$search%")
                              ->orWhere(function($q) use($data, $search){
                                   $q->where('status', $data['status'])
                                      ->where('issue_to', 'like', "%$search%");
                              })
                              ->paginate($data['paginate']);
           }
         } else {
           $epins = Epin::where('status', $data['status'])->where('issue_to', auth()->id())->paginate($data['paginate']);

           $search = $data['search'];

           if ($data['search'] || $data['search'] !=null) {
               $epins = Epin::where('status', $data['status'])
                              ->where('issue_to', auth()->id())
                              ->where('epin', 'like', "%$search%")
                              ->orWhere(function($q) use($data, $search){
                                    $q->where('status', $data['status'])
                                      ->where('issue_to', auth()->id())
                                      ->where('issue_to', 'like', "%$search%");
                              })
                              ->paginate($data['paginate']);
           }
         }

         return $epins;
     }

     public static function create($data)
     {
        if (!auth()->user()->can('create-epins')) {
           \App::abort(403, 'you are not authorized to create epins');
        }
         self::validator($data)->validate();
         if (self::store($data)) {
            return true;
         }
         return false;
     }

     public static function store($data)
     {
         for ($i = 0; $i < trim($data['count']) ; $i++) {
             Epin::create([
                   'epin'         => rand(999999, 000000),
                   'issue_to'     => trim($data['issue_to']),
                   'amount'       => trim($data['amount']),
                   'generated_by' => Auth::id(),
                   'type'         => trim($data['type'])
             ]);
         }
         return true;
     }

     public static function edit($id)
     {
         if (!auth()->user()->can('edit-epins')) {
            \App::abort(403, 'you are not authorized to edit epins');
         }
         return Epin::find($id);
     }

     /**
      * update epin
      * @param  arrey $data id for query
      * @return response boolean
      */
     public static function update($data)
     {
         if ($epin = Epin::find($data['id'])) {
           $data['count'] = 1;
           self::validator($data)->validate();
           return $epin->update([
                'amount' => $data['amount'],
                'type'   => $data['type'],
                'issue_to' => $data['issue_to']
           ]);
        }

        return false;
     }

     public static function destroy($id)
     {
       if (!auth()->user()->can('delete-epins')) {
          \App::abort(403, 'you are not authorized to delete epins');
       }
       if ($epin = Epin::find($id)) {
           return $epin->delete();
       }
       return false;
     }

     public static function validator($data)
     {
       /**
        * make validator for $data
        * @var array
        * @return response $validator
        */
        $validator = Validator::make($data, self::rules($data));

        /**
         * check if the issue to userid is avaiable in the database or not
         * @var string $data['issue_to']
         */
         if (isset($data['issue_to'])) {
           if (!self::chkuser($data['issue_to'])) {
             /**
             * the from issue_to user not found
              * add custom error to validator errobag
              * @var array issue_to error
              */
               $validator->after(function($validator){
                    $validator->errors()->add('issue_to', ucfirst(trans('message.notfound', ['attr' => 'userid'])));
               });
           }
         }

         /**
          * if the action is called from transfer the
          * check if the from userid available or not
          * @var string $data['from']
          */
         if (isset($data['transfer'])) {

           if (!self::chkuser($data['from'])) {
             /**
              * the from user not found
              * add custom error to validator errobag
              * @var array issue_to error
              */
               $validator->after(function($validator){
                    $validator->errors()->add('from', ucfirst(trans('message.notfound', ['attr' => 'userid'])));
               });
           }

           /**
           * check if the from user and the issue_to user same
           * then abort
           * @var array
           */
           if (trim($data['from']) == trim($data['issue_to'])) {
             /**
             * add custom error to validator errobag
             * @var array issue_to error
             */
             $validator->after(function($validator){
               $validator->errors()->add('issue_to', ucfirst(trans('epin.sameid_not_transfer')));
             });
           }
           /**
           * check if the from user has engough amount of epins or not
           * @var number $avaiable_qty
           */
           $avaiable_qty = Epin::where([
             'amount' => $data['amount'],
             'issue_to' => $data['from'],
             'status' => 'un-use'
             ])->count();

             if ($avaiable_qty < trim($data['count'])) {
               /**
               * add custom error to validator errobag
               * @var array issue_to error
               */
               $validator->after(function($validator) use($avaiable_qty, $data){
                 $validator->errors()->add('from', ucfirst(trans('epin.not_engough_epins',
                 [ 'count' => $avaiable_qty, 'amount' => $data['amount'], 'currency' => config('aiomlm.company.currency') ])));
               });
             }
         }

         return $validator;
    }

    public static function chkuser($id)
    {
       return User::role('member')->find(trim($id));
    }

     public static function rules($data)
     {
        if (isset($data['transfer'])) {
          if ($data['transfer']) {
             return [
                 'amount' => 'required|integer|max:10000000|min:1',
                 'issue_to' => 'required|integer',
                 'count' => 'required|integer|max:999',
                 'from' => 'required|integer',
             ];
          }
       }
         return [
             'amount' => 'required|integer|max:10000000|min:1',
             'issue_to' => 'required|integer',
             'count' => 'required|integer|max:999',
             'type' => 'required|in:single-use,multi-use',
         ];
     }

     public static function transfer($data)
     {
       if (!auth()->user()->can('transfer-epins')) {
          \App::abort(403, 'you are not authorized to transfer epins');
       }
        $data['transfer'] = true;
        self::validator($data)->validate();
        /**
         * finally transfer the epins as we have done validation
         * @var array $data
         * @return boolean true(1) or false(0)
         * transfer can be varified by true or false at caller end
         */
        return $epins = Epin::where([
            'amount' => $data['amount'],
            'issue_to' => $data['from'],
            'status'   => 'un-use'
         ])->take($data['count'])->update([
            'issue_to' => $data['issue_to'],
            'transfer_by' => \Auth::id(),
            'transfer_time' => now()
         ]);
     }

     /**
      * delete epins bulkly
      * @param  array $data array must be key => id
      * @return boolean
      */
     public static function bulkDelete($data)
     {
       if (!auth()->user()->can('delete-epins')) {
          \App::abort(403, 'you are not authorized to delete epins');
       }
        foreach ($data as $key => $id) {
           if ($epin = Epin::where('id', $id)->where('status', 'un-use')->first()) {
              $epin->delete();
           }
        }

        return true;
     }

}
