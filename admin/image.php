<?php 
/**
 * Images
 *
 * Displays information on the passed image
 *
 * @package GetSimple
 * @subpackage Images
 */

// Setup inclusions
$load['plugin'] = true;

// Include common.php
include('inc/common.php');
login_cookie_check();

exec_action('load-image');

// Variable Settings
$subPath = (isset($_GET['path'])) ? $_GET['path'] : "";
if ($subPath != '') $subPath = tsl($subPath);

$uploadsPath      = GSDATAUPLOADPATH;
$uploadsPathRel   = getRelPath(GSDATAUPLOADPATH);
$thumbPathRel     = getRelPath(GSTHUMBNAILPATH);

$src              = strippath($_GET['i']);
$thumb_folder     = GSTHUMBNAILPATH.$subPath;
$src_folder       = $uploadsPath;
$src_url          = tsl($SITEURL).$uploadsPathRel.$subPath;
$thumb_folder_rel = $thumbPathRel.$subPath;
$thumb_url        = tsl($SITEURL).$thumb_folder_rel;

if (!is_file($src_folder . $subPath .$src)) redirect("upload.php");

// handle jcrop thumbnail creation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	require_once('inc/imagemanipulation.php');
	$objImage = new ImageManipulation($src_folder . $subPath .$src);
	if ( $objImage->imageok ) {
		$objImage->setCrop($_POST['x'], $_POST['y'], $_POST['w'], $_POST['h']);
		//$objImage->show();
		$objImage->save($thumb_folder . 'thumbnail.' .$src);
		$success = i18n_r('THUMB_SAVED');
	} else {
		i18n('ERROR');
	}
}

$thumb_exists = $thwidth = $thheight = $thtype = $athttr = '';

list($imgwidth, $imgheight, $imgtype, $imgattr) = getimagesize($src_folder .$subPath. $src);

if (file_exists($thumb_folder . 'thumbnail.' . $src)) {
	list($thwidth, $thheight, $thtype, $athttr) = getimagesize($thumb_folder . 'thumbnail.'.$src);
	$thumb_exists = ' &nbsp; | &nbsp; <a href="'.$thumb_url . 'thumbnail.'. rawurlencode($src) .'" rel="facybox_i" >'.i18n_r('CURRENT_THUMBNAIL').'</a> <code>'.$thwidth.'x'.$thheight.'</code>';
}else{
	// if thumb is missing recreate it
	require_once('inc/imagemanipulation.php');
	if(genStdThumb($subPath,$src)){
		list($thwidth, $thheight, $thtype, $athttr) = getimagesize($thumb_folder . 'thumbnail.'.$src);
		$thumb_exists = ' &nbsp; | &nbsp; <a href="'.$thumb_url . 'thumbnail.'. rawurlencode($src) .'" rel="facybox_i" >'.i18n_r('CURRENT_THUMBNAIL').'</a> <code>'.$thwidth.'x'.$thheight.'</code>';
	}
}

$pagetitle = i18n_r('IMAGES').' &middot; '.var_out($src).' &middot; '.i18n_r('FILE_MANAGEMENT');
get_template('header');

include('template/include-nav.php'); ?>

<div class="bodycontent clearfix">
	<div id="maincontent">
			
		<div class="main">
		<h3 class="floated"><?php i18n('IMG_CONTROl_PANEL');?></h3>
		<div class="edit-nav clearfix" >
			<?php exec_action(get_filename_id().'-edit-nav'); ?>
		</div>		
		<?php exec_action(get_filename_id().'-body'); ?>		
			<?php echo '<p><a href="'.$src_url .rawurlencode($src).'" rel="facybox_i" >'.i18n_r('ORIGINAL_IMG').'</a> <code>'.$imgwidth.'x'.$imgheight .'</code>'. $thumb_exists .'</p>'; ?>

			<form>
				<select class="text" id="img-info" style="width:50%" >
					<option selected value="code-img-link" ><?php i18n('LINK_ORIG_IMG');?></option>
					<option value="code-img-html" ><?php i18n('HTML_ORIG_IMG');?></option>
					<?php if(!empty($thumb_exists)) { ?>
					<option value="code-thumb-html" ><?php i18n('HTML_THUMBNAIL');?></option>
					<option value="code-thumb-link" ><?php i18n('LINK_THUMBNAIL');?></option>
					<option value="code-imgthumb-html" ><?php i18n('HTML_THUMB_ORIG');?></option>
					<?php } ?>
				</select>
				<textarea class="copykit" ><?php echo $src_url. rawurlencode($src); ?></textarea>
				<p style="color:#666;font-size:11px;margin:-10px 0 0 0"><a href="javascript:void(0)" class="select-all" ><?php i18n('CLIPBOARD_INSTR');?></a></p>
			</form>
			<div class="toggle">
				<p id="code-img-html">&lt;img src="<?php echo $src_url. rawurlencode($src); ?>" class="gs_image" height="<?php echo $imgheight; ?>" width="<?php echo $imgwidth; ?>" alt=""></p>
				<p id="code-img-link"><?php echo $src_url. rawurlencode($src); ?></p>
				<?php if(!empty($thumb_exists)) { ?>
				<p id="code-thumb-html">&lt;img src="<?php echo $thumb_url.'thumbnail.'. rawurlencode($src); ?>" class="gs_image gs_thumb" height="<?php echo $thheight; ?>" width="<?php echo $thwidth; ?>" alt=""></p>
				<p id="code-thumb-link"><?php echo $thumb_url.'thumbnail.'.rawurlencode($src); ?></p>
				<p id="code-imgthumb-html">&lt;a href="<?php echo $src_url. rawurlencode($src); ?>" class="gs_image_link" >&lt;img src="<?php echo $thumb_url.'thumbnail.'.rawurlencode($src); ?>" class="gs_thumb" height="<?php echo $thheight; ?>" width="<?php echo $thwidth; ?>" alt="" />&lt;/a></p>
				<?php } ?>
			</div>
	</div>

<?php
$jcrop = !empty($thumb_exists);
if($jcrop){ ?>
	<div id="jcrop_open" class="main">
	    <img src="<?php echo $src_url .rawurlencode($src); ?>" id="cropbox" />
			<div id="handw" class="toggle" ><?php i18n('SELECT_DIMENTIONS'); ?><br /><span id="picw"></span> x <span id="pich"></span></div>
	    <!-- This is the form that our event handler fills -->
	    <form id="jcropform" action="<?php myself(); ?>?i=<?php echo rawurlencode($src); ?>&amp;path=<?php echo $subPath; ?>" method="post" onsubmit="return checkCoords();">
	      <input type="hidden" id="x" name="x" />
	      <input type="hidden" id="y" name="y" />
	      <input type="hidden" id="w" name="w" />
	      <input type="hidden" id="h" name="h" />
	      <input type="submit" class="submit" value="<?php i18n('CREATE_THUMBNAIL');?>" /> &nbsp; <span style="color:#666;font-size:11px;"><?php i18n('CROP_INSTR_NEW');?></span>

	    </form>
	</div>
	
<?php } ?>
	</div>
	
	<div id="sidebar" >
		<?php include('template/sidebar-files.php'); ?>
	</div>	

	<script>
	  jQuery(document).ready(function() { 
	    		
			$(window).load(function(){
				var api = $.Jcrop('#cropbox',{
			    onChange: updateCoords,
			    onSelect: updateCoords,
			    boxWidth: 585, 
			    boxHeight: 500
			  }); 
			  var isCtrl = false;
				$(document).keyup(function (e) {
					api.setOptions({ aspectRatio: 0 });
					api.focus();
					if(e.which == 17) isCtrl=false;
				}).keydown(function (e) {
					if(e.which == 17) isCtrl=true;
					if(e.which == 66 && isCtrl == true) {
						api.setOptions({ aspectRatio: 1 });
						api.focus();
					}
				});
			});
		
		});
	</script>
	
	</div>
<?php get_template('footer'); ?>
