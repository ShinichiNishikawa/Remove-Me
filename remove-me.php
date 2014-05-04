<?php
/*
Plugin Name: Remove Me
Plugin URI: https://github.com/ShinichiNishikawa/Remove-Me
Description: This is a plugin to allow users to remove their own account.
Author: Shinichi Nishikawa
Version: 0.0
Author URI: http://nskw-style.com
*/

add_action( 'init', 'remove_me' );
function remove_me() {
	
	// マルチサイトはそのまま返す
	if ( is_multisite() )
		return;
	
	// ログインしていないのはそのまま返す
	if ( !is_user_logged_in() )
		return;

	// POSTでremove_meが渡されていないならそのまま返す
	if ( !isset( $_POST['remove_me'] ) || $_POST['remove_me'] !== 'sure' )
		return;

	// 以降、シングルインストールで、
	// ログインユーザーによって$_POST['remove_me']が投げられた場合

	// 他のユーザーを管理画面で削除することができる＝管理者の場合
	if ( current_user_can( 'delete_users' ) )
		wp_die( '管理者は自分を削除できません。管理画面からどうぞ' );

	// $_POST['remove_me'] があるのに送信者のユーザIDがないのはダメ
	if ( !isset( $_POST['senders_id'] ) || '' == $_POST['senders_id'] )
		wp_die( '不正な操作が行われました。' );

	// 整数かどうか確認する
	$senders_id = intval( $_POST['senders_id'], 10 );
	if ( ! $senders_id )
		wp_die( '不正な操作が行われました。' );

	// 表示中のログインユーザーIDと送られてきたユーザーIDを比較
	$current_user = wp_get_current_user();
	if ( $current_user->ID != $senders_id )
		wp_die( '不正な操作が行われました。' );
	
	if ( !check_admin_referer( 'randtext', '_my_nonce' ) )
		return;

	// 削除する
	require_once( ABSPATH.'wp-admin/includes/user.php' );
	$deleted = wp_delete_user( $current_user->ID, 1 );
	
	if ( $deleted ) {
		wp_die( '退会処理を実行しました。' );
	} else {
		wp_die( '失敗しました。' );
	}
		
}

// 自分を削除するためのボタンを表示する
function display_remove_me_form() {

	// マルチサイトは返す
	if ( is_multisite() )
		return;
	
	if ( ! is_user_logged_in() )
		wp_die( 'この画面はログインユーザー専用の画面です。<br><a href="' . home_url('/') . '">トップページへ戻る</a>' );
	
	// 他のユーザーを管理画面で削除することができる＝管理者の場合はそのまま返す
	if ( current_user_can( 'delete_users' ) )
		return;

	$current_user = wp_get_current_user();
	$senders_id = $current_user->ID;
	?>
<form id="remove-me" method="post" action="">
	<label for="remove_me">本当に削除しますか？</label>
	<input type="checkbox" id="remove_me" name="remove_me" value="sure" />
	<input type="hidden" id="senders_id" name="senders_id" value="<?php echo (int)$senders_id; ?>">
	<?php wp_nonce_field( 'randtext', '_my_nonce' ); ?>
	<br />
	<input type="submit" value="自分のアカウントを削除する" />
</form>
	<?php
	
}