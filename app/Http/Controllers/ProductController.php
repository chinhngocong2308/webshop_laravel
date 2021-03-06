<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AdminRequestProduct;
use DB;
use App\Http\Requests;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use App\Models\CatePost;
use Session;
use App\Models\Product;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Rating;
session_start();
class ProductController extends Controller
{
     public function AuthLogin(){
        $admin_id = Session::get('admin_id');
        if($admin_id){
            return Redirect::to('admin.dashboard');
        }
        else{
            return Redirect::to('admin')->send();
        }
    }
   public function add_product()
    {
        $this->AuthLogin();
    	$cate_product = DB::table('tbl_category_product')->orderby('category_id','desc')->get();
    	$brand_product = DB::table('tbl_brand')->orderby('brand_id','desc')->get();
    	
    	return view('admin.add_product')->with('cate_product', $cate_product)->with('brand_product',$brand_product);
    }
     public function all_product()
    {	
        $this->AuthLogin();
    	$all_product = DB::table('tbl_product')
        ->join('tbl_category_product','tbl_category_product.category_id','=','tbl_product.category_id')
        ->join('tbl_brand','tbl_brand.brand_id','=','tbl_product.brand_id')
        ->orderby('tbl_product.product_id','desc')->paginate(3);
        $manager_product  = view('admin.all_product')->with('all_product',$all_product);
        return view('admin_layout')->with('admin.all_product', $manager_product);
    }
    public function save_product(AdminRequestProduct $request) // request lay yeu cau giu lieu
    {
        $this->AuthLogin();
    	$data = array();
    	$data['product_name'] = $request->product_name;
    	//Tên cột trong bảng tbl  //name thuộc tính trong add_cate..method=POST
    	$data['product_desc'] = $request->product_desc;
    	$data['product_price'] = $request->product_price;
        $data['product_slug'] = $request->product_slug;
        $data['product_sale'] = $request->product_sale;
    	$data['product_content'] = $request->product_content;
    	$data['category_id'] = $request->product_cate;
        $data['brand_id'] = $request->product_brand;
        $data['brand_parent_id'] = $request->product_parent_id;
    	$data['product_status'] = $request->product_status;
        $data['product_image'] = $request->product_image;
        $get_image = $request->file('product_image');
      
        if($get_image){
            $get_name_image = $get_image->getClientOriginalName();
            $name_image = current(explode('.',$get_name_image));
            $new_image =  $name_image.rand(0,99).'.'.$get_image->getClientOriginalExtension();
            $get_image->move('public/uploads/product',$new_image);
            $data['product_image'] = $new_image;
            DB::table('tbl_product')->insert($data);
            Session::put('message','Thêm sản phẩm thành công');
            return Redirect::to('add-product');
        }
        $data['product_image'] = '';
        DB::table('tbl_product')->insert($data);
        Session::put('message','Thêm sản phẩm thành công');
        return Redirect::to('all-product');
    }
    public function active_product($product_id){
    	DB::table('tbl_product')->where('product_id',$product_id)->update(['product_status'=>0]);
    	
    	return Redirect::to('add-product');

    }
    public function unactive_product($product_id){
    	DB::table('tbl_product')->where('product_id',$product_id)->update(['product_status'=>1]);
    	
    	return Redirect::to('all-product');
    }
    public function edit_product($product_id){
        $this->AuthLogin();
        $cate_product = DB::table('tbl_category_product')->orderby('category_id','desc')->get();
        $brand_product = DB::table('tbl_brand')->orderby('brand_id','desc')->get();
        
    	$edit_product = DB::table('tbl_product')->where('product_id',$product_id)->get();
    	$manager_product = view('admin.edit_product')->with('edit_product',$edit_product)->with('cate_product',$cate_product)->with('brand_product',$brand_product);
    	return view('admin_layout')->with('admin.edit_product',$manager_product);
    }
    public function update_product(Request $request, $product_id){
        $this->AuthLogin();
    	$data = array();
    	$data['product_name'] = $request->product_name;
        //Tên cột trong bảng tbl  //name thuộc tính trong add_cate..method=POST
        $data['product_desc'] = $request->product_desc;
        $data['product_slug'] = $request->product_slug;
        $data['product_price'] = $request->product_price;
        $data['product_sale'] = $request->product_sale;
        $data['product_content'] = $request->product_content;
        $data['category_id'] = $request->product_cate;
        $data['brand_id'] = $request->product_brand;
        $data['brand_parent_id'] = $request->product_parent_id;
        $data['product_status'] = $request->product_status;
        $get_image = $request->file('product_image');
      
        if($get_image){
                    $get_name_image = $get_image->getClientOriginalName();
                    $name_image = current(explode('.',$get_name_image));
                    $new_image =  $name_image.rand(0,99).'.'.$get_image->getClientOriginalExtension();
                    $get_image->move('public/uploads/product',$new_image);
                    $data['product_image'] = $new_image;
                    DB::table('tbl_product')->where('product_id',$product_id)->update($data);
                    Session::put('message','Cập nhật sản phẩm thành công');
                    return Redirect::to('all-product');
        }
            
        DB::table('tbl_product')->where('product_id',$product_id)->update($data);
        Session::put('message','Cập nhật sản phẩm thành công');
        return Redirect::to('all-product');
    }
     public function delete_product(Request $request,$product_id){
        $this->AuthLogin();
        DB::table('tbl_product')->where('product_id',$product_id)->delete();
        Session::put('message','Xóa sản phẩm thành công!');
       
        return Redirect::to('all-product');
    }
    // end admin pages
    public function details_product($product_id , Request $request){
     $cate_product = DB::table('tbl_category_product')->where('category_status','0')->orderby('category_id','desc')->get(); 
       $category_post = CatePost::orderby('cate_post_id','DESC')->get();
        $brand_product = DB::table('tbl_brand')->where('brand_status','0')->orderby('brand_id','desc')->get(); 

        $images_product = DB::table('tbl_images_product')->orderby('image_id','desc')->get();
          $details_product = DB::table('tbl_product')
        ->join('tbl_category_product','tbl_category_product.category_id','=','tbl_product.category_id')
        ->join('tbl_brand','tbl_brand.brand_id','=','tbl_product.brand_id')
        ->where('tbl_product.product_id',$product_id)->get();

        $image = DB::table('tbl_images_product')->select('image_id','imagesp')->where('product_id',$product_id)->get();
      foreach($details_product as $key => $value){
             $category_id = $value->category_id;
                //seo 
                $meta_desc = $value->product_desc;
                $meta_keywords = $value->product_slug;
                $meta_title = $value->product_name;
                $url_canonical = $request->url();
                //--seo
            }
        $related_product = DB::table('tbl_product')
        ->join('tbl_category_product','tbl_category_product.category_id','=','tbl_product.category_id')
        ->join('tbl_brand','tbl_brand.brand_id','=','tbl_product.brand_id')
        ->where('tbl_category_product.category_id',$category_id)->whereNotIn('tbl_product.product_id',[$product_id])->get();
        $rating = Rating::where('product_id',$product_id)->avg('rating');
        $rating = round($rating);

         return view('pages.product.show_details')->with('category',$cate_product)->with('brand',$brand_product)->with('product_details',$details_product)->with('related',$related_product)->with('meta_desc',$meta_desc)->with('meta_keywords',$meta_keywords)->with('meta_title',$meta_title)->with('url_canonical',$url_canonical)->with('image',$image)->with('category_post',$category_post)->with('rating',$rating);
        
    
    }
    public function quickview(Request $request,$product_id){
       $product_id = $request->product_id;
       $product = Product::find($product_id);
       $output['product_name'] = $product->product_name;
       $output['product_id'] = $product->product_id;
       $output['product_desc'] = $product->product_desc;
       $output['product_content'] = $product->product_content;
       $output['product_price'] = number_format($product->product_price,0,',','.').'VNĐ';
       $output['product_image'] = '<p><img width="100%" src="public/uploads/product/'.$product->product_image.'"></p>';

       echo json_encode($output);
    }
    public function load_comment(Request $request){
        $product_id =$request->product_id;
        $comment = Comment::where('comment_product_id',$product_id)->where('comment_parent_comment','=',0)->where('comment_status',0)->orderby('comment_id','desc')->get();
         $comment_rep = Comment::with('product')->where('comment_parent_comment','>',0)->get();
        $output = '';
        foreach ($comment as $key => $comm){
            $output.= '
                        <div class="comments-details">
                            <div class="comments-list-img">
                                                            
                                <img src="'.url('/public/frontend/img/21104.png').'" alt="" style="width:50px;height:50px">
                            </div>
                            <div class="comments-content-wrap">
                                <span>
                                    <b><a href="#">@'.$comm->comment_name.'</a></b>
                                    <span class="post-time">'.$comm->created_at.'</span>
                                </span>
                                <p>'.$comm->comment.'</p>
                            </div>
                        </div>
                         ';
                        foreach ($comment_rep as $key => $rep_comment) {
                            if($rep_comment->comment_parent_comment==$comm->comment_id){
                        $output.='<div class="comments-details" >
                            <div class="comments-list-img" >
                                <img  src="'.url('/public/frontend/img/chỉ mục.jpg').'" alt="" style="width:50px;height:50px;margin-left:135px;margin-top:10px">
                            </div>
                            <div class="comments-content-wrap" style="margin-left:120px" >
                                <span>
                                    <b><a href="#">'.$rep_comment->comment_name.'</a></b>
                                    <span class="post-time">'.$rep_comment->created_at.'</span>
                                </span>
                                <p>'.$rep_comment->comment.'</p>
                            </div>
                        </div> ';
                      }
                   }
               }
             echo $output;
       
    }
    public function send_comment(Request $request){
        $product_id =$request->product_id;
        $comment_name =$request->comment_name;
        $comment_content =$request->comment_content;
        $comment_email =$request->comment_email;
        $comment = new Comment();
        $comment->comment = $comment_content;
        $comment->comment_name = $comment_name;
        $comment->comment_email = $comment_email;
        $comment->comment_product_id = $product_id; 
        $comment->comment_status = 1 ;
        $comment->comment_parent_comment = 0 ;
        $comment->save();
    }
    //comment admin
    public function list_comment(){
        $comment = Comment::with('product')->where('comment_parent_comment','=',0)->orderby('comment_id','desc')->get();
        $comment_rep = Comment::with('product')->where('comment_parent_comment','>',0)->get();
        return view('admin.comment.list_comment')->with(compact('comment','comment_rep'));
    }
    public function duyet_comment(Request $request){
        $data = $request->all();
        $comment = Comment::find($data['comment_id']);
        $comment->comment_status = $data['comment_status'];
        $comment->save();
    }
    public function reply_comment(request $request){
         $data = $request->all();
          $comment = new Comment;
          $comment->comment = $data['comment'];
          $comment->comment_product_id=$data['comment_product_id'];
          $comment->comment_parent_comment=$data['comment_id'];
          $comment->comment_status = 0;
          $comment->comment_email = 'chinhngocong2308@gmail.com';
          $comment->comment_name = 'Chính Admin';
          $comment->save();

    }
    public function insert_rating(Request $request){
        $data = $request->all();
        $rating = new Rating();
        $rating->product_id = $data['product_id'];
        $rating->rating = $data['index'];
        $rating->save();
        echo 'done';
    }
}
