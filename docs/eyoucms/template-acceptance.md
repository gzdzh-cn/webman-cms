# 原包218模板逐文件验收

生成于 2026-11-02T20:42:35.973891+00:00。执行依据：`runtime/compat/template-pages.json`，SHA256 `4a7b045f81686dd2e063bab5830f5baf3a8283ed8f7cf91b8bd67193b5bb784f`。

每一行对应一个原始模板文件。独立页面使用真实控制器、独立提交的栏目/文档数据与HTTP请求；公共片段通过成功父页面和固定HTML标记验证。会员/商城明确排除，不计作通过。

## 统计

| 分类 | 数量 |
|---|---:|
| shared_enterprise_fragment | 6 |
| enterprise_standalone_page | 44 |
| enterprise_ajax_fragment | 2 |
| excluded_system_member_commerce | 7 |
| excluded_member_commerce | 159 |

| 验收状态 | 数量 |
|---|---:|
| included_in_actual_http_verified | 6 |
| actual_http_passed | 44 |
| actual_http_pc_fallback_verified | 2 |
| excluded_not_executed | 166 |

## 逐文件

| 原模板 | 分类 | 状态 | 原因/执行依据 |
|---|---|---|---|
| `template/mobile/banner.htm` | shared_enterprise_fragment | included_in_actual_http_verified | 非独立页面；原样由同设备企业页面include，并在实际成功HTTP响应中验证固定HTML标记。 父页面：lists_article.htm, lists_product.htm, lists_images.htm, view_images.htm, lists_download.htm, lists_media.htm, lists_single.htm, lists_recruit.htm, lists_article_img.htm, lists_single_contact.htm |
| `template/mobile/footer.htm` | shared_enterprise_fragment | included_in_actual_http_verified | 非独立页面；原样由同设备企业页面include，并在实际成功HTTP响应中验证固定HTML标记。 父页面：index.htm, index_tags.htm, lists_tags.htm, lists_search.htm, lists_article.htm, view_article.htm, lists_product.htm, view_product.htm, lists_images.htm, view_images.htm, lists_download.htm, view_download.htm, lists_media.htm, view_media.htm, lists_single.htm, lists_recruit.htm, view_recruit.htm, lists_article_img.htm, lists_single_contact.htm |
| `template/mobile/header.htm` | shared_enterprise_fragment | included_in_actual_http_verified | 非独立页面；原样由同设备企业页面include，并在实际成功HTTP响应中验证固定HTML标记。 父页面：index.htm, index_tags.htm, lists_tags.htm, lists_search.htm, lists_article.htm, view_article.htm, lists_product.htm, view_product.htm, lists_images.htm, view_images.htm, lists_download.htm, view_download.htm, lists_media.htm, view_media.htm, lists_single.htm, lists_recruit.htm, view_recruit.htm, lists_article_img.htm, lists_single_contact.htm |
| `template/mobile/index.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__index.htm.html` |
| `template/mobile/index_tags.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__index_tags.htm.html` |
| `template/mobile/lists_article.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__lists_article.htm.html` |
| `template/mobile/lists_article_img.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__lists_article_img.htm.html` |
| `template/mobile/lists_download.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__lists_download.htm.html` |
| `template/mobile/lists_guestbook.htm` | enterprise_standalone_page | actual_http_pc_fallback_verified | 原始mobile文件为0字节；实际HTTP明确回退同主题PC对应模板。响应头与PC源文件hash已校验，未修改mobile源文件。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__lists_guestbook.htm.html` |
| `template/mobile/lists_images.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__lists_images.htm.html` |
| `template/mobile/lists_media.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__lists_media.htm.html` |
| `template/mobile/lists_product.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__lists_product.htm.html` |
| `template/mobile/lists_recruit.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__lists_recruit.htm.html` |
| `template/mobile/lists_search.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__lists_search.htm.html` |
| `template/mobile/lists_single.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__lists_single.htm.html` |
| `template/mobile/lists_single_contact.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__lists_single_contact.htm.html` |
| `template/mobile/lists_special.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__lists_special.htm.html` |
| `template/mobile/lists_tags.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__lists_tags.htm.html` |
| `template/mobile/system/arclist_block001.htm` | enterprise_ajax_fragment | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__system__arclist_block001.htm.json` |
| `template/mobile/system/comment_list.htm` | excluded_system_member_commerce | excluded_not_executed | 会员信息或会员评论片段，依赖排除的会员/评论业务。 |
| `template/mobile/system/product_comment.htm` | excluded_system_member_commerce | excluded_not_executed | 会员信息或会员评论片段，依赖排除的会员/评论业务。 |
| `template/mobile/system/users_info.htm` | excluded_system_member_commerce | excluded_not_executed | 会员信息或会员评论片段，依赖排除的会员/评论业务。 |
| `template/mobile/users/answer_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/article_add.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/article_download_add.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/article_download_edit.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/article_edit.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/article_images_add.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/article_images_edit.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/article_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/article_media_add.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/article_media_edit.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/article_order_details.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/ask_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/bind_email.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/bind_mobile.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/channel_bar.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/download_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/level_centre.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/media_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/media_order_details.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/pay_account_recharge.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/pay_consumer_details.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/pay_error.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/pay_recharge_detail.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/pay_success.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/pay_wechat.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/release_bar.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/release_centre.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/release_field.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/reset_password.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/reset_password_mobile.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/retrieve_password.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/retrieve_password_mobile.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/shop_add_address.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/shop_address_list.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/shop_after_service.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/shop_after_service_apply.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/shop_after_service_details.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/shop_after_service_list.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/shop_cart_list.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/shop_centre.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/shop_comment_goods_list.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/shop_comment_list.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/shop_comment_product.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/shop_edit_address.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/shop_get_wechat_addr.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/shop_need_comment_list.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/shop_order_details.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/shop_query_express.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/shop_select_verify_store.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/shop_to_split_goods.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/shop_under_order.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/shop_wechat_pay_select.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/skin/css/diy_css.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_article_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_centre.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_centre_field.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_change_pwd.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_collection_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_download_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_footer.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_footprint_header.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_footprint_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_gift_detail.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_gift_list.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_gift_shiwu.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_header.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_left.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_login.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_media_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_media_play_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_notice_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_order_header.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_reg.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_reg_field.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_release_field_m.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_score_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_select_login.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/users/users_welcome.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/mobile/view_article.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__view_article.htm.html` |
| `template/mobile/view_download.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__view_download.htm.html` |
| `template/mobile/view_images.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__view_images.htm.html` |
| `template/mobile/view_media.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__view_media.htm.html` |
| `template/mobile/view_product.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__view_product.htm.html` |
| `template/mobile/view_recruit.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__view_recruit.htm.html` |
| `template/mobile/view_special.htm` | enterprise_standalone_page | actual_http_pc_fallback_verified | 原始mobile文件为0字节；实际HTTP明确回退同主题PC对应模板。响应头与PC源文件hash已校验，未修改mobile源文件。 HTTP 200；`runtime/compat/template-pages/cp058c07/mobile__view_special.htm.html` |
| `template/pc/banner.htm` | shared_enterprise_fragment | included_in_actual_http_verified | 非独立页面；原样由同设备企业页面include，并在实际成功HTTP响应中验证固定HTML标记。 父页面：lists_article.htm, lists_product.htm, lists_images.htm, lists_download.htm, lists_media.htm, lists_single.htm, lists_recruit.htm, lists_article_img.htm, lists_single_contact.htm |
| `template/pc/footer.htm` | shared_enterprise_fragment | included_in_actual_http_verified | 非独立页面；原样由同设备企业页面include，并在实际成功HTTP响应中验证固定HTML标记。 父页面：index.htm, index_tags.htm, lists_tags.htm, lists_search.htm, lists_article.htm, view_article.htm, lists_product.htm, view_product.htm, lists_images.htm, view_images.htm, lists_download.htm, view_download.htm, lists_media.htm, view_media.htm, lists_single.htm, lists_recruit.htm, view_recruit.htm, lists_article_img.htm, lists_single_contact.htm |
| `template/pc/header.htm` | shared_enterprise_fragment | included_in_actual_http_verified | 非独立页面；原样由同设备企业页面include，并在实际成功HTTP响应中验证固定HTML标记。 父页面：index.htm, index_tags.htm, lists_tags.htm, lists_search.htm, lists_article.htm, view_article.htm, lists_product.htm, view_product.htm, lists_images.htm, view_images.htm, lists_download.htm, view_download.htm, lists_media.htm, view_media.htm, lists_single.htm, lists_recruit.htm, view_recruit.htm, lists_article_img.htm, lists_single_contact.htm |
| `template/pc/index.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__index.htm.html` |
| `template/pc/index_tags.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__index_tags.htm.html` |
| `template/pc/lists_article.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__lists_article.htm.html` |
| `template/pc/lists_article_img.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__lists_article_img.htm.html` |
| `template/pc/lists_download.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__lists_download.htm.html` |
| `template/pc/lists_guestbook.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__lists_guestbook.htm.html` |
| `template/pc/lists_images.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__lists_images.htm.html` |
| `template/pc/lists_media.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__lists_media.htm.html` |
| `template/pc/lists_product.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__lists_product.htm.html` |
| `template/pc/lists_recruit.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__lists_recruit.htm.html` |
| `template/pc/lists_search.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__lists_search.htm.html` |
| `template/pc/lists_single.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__lists_single.htm.html` |
| `template/pc/lists_single_contact.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__lists_single_contact.htm.html` |
| `template/pc/lists_special.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__lists_special.htm.html` |
| `template/pc/lists_tags.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__lists_tags.htm.html` |
| `template/pc/system/arclist_block001.htm` | enterprise_ajax_fragment | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__system__arclist_block001.htm.json` |
| `template/pc/system/article_pay.htm` | excluded_system_member_commerce | excluded_not_executed | 付费阅读/下载支付片段，属于排除的商城/付费流程。 |
| `template/pc/system/download_pay.htm` | excluded_system_member_commerce | excluded_not_executed | 付费阅读/下载支付片段，属于排除的商城/付费流程。 |
| `template/pc/system/product_comment.htm` | excluded_system_member_commerce | excluded_not_executed | 会员信息或会员评论片段，依赖排除的会员/评论业务。 |
| `template/pc/system/users_info.htm` | excluded_system_member_commerce | excluded_not_executed | 会员信息或会员评论片段，依赖排除的会员/评论业务。 |
| `template/pc/users/answer_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/article_add.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/article_download_add.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/article_download_edit.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/article_edit.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/article_images_add.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/article_images_edit.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/article_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/article_media_add.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/article_media_edit.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/article_order_details.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/ask_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/bind_email.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/bind_mobile.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/channel_bar.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/download_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/finance_bar.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/level_centre.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/media_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/media_order_details.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/pay_account_recharge.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/pay_consumer_details.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/pay_error.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/pay_recharge_detail.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/pay_success.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/pay_wechat.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/release_bar.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/release_centre.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/release_field.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/reset_password.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/reset_password_mobile.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/retrieve_password.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/retrieve_password_mobile.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/shop_add_address.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/shop_address_list.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/shop_after_service.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/shop_after_service_apply.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/shop_after_service_details.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/shop_after_service_list.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/shop_cart_header.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/shop_cart_list.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/shop_centre.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/shop_comment_goods_list.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/shop_comment_list.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/shop_comment_product.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/shop_edit_address.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/shop_get_wechat_addr.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/shop_need_comment_list.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/shop_order_details.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/shop_query_express.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/shop_select_verify_store.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/shop_service_list.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/shop_to_split_goods.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/shop_under_order.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/shop_wechat_pay_select.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/skin/css/diy_css.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_article_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_centre.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_centre_field.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_change_pwd.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_collection_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_download_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_footer.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_footprint_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_gift_detail.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_gift_list.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_gift_shiwu.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_header.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_left.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_login.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_media_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_media_play_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_notice_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_reg.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_reg_field.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_release_field.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_score_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_select_login.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_welcome.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_welcome_field.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/users/users_youhuijuan_index.htm` | excluded_member_commerce | excluded_not_executed | 用户中心、登录注册、投稿、会员下载/视频或商城交易页面及其片段；用户明确排除会员和商城。 |
| `template/pc/view_article.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__view_article.htm.html` |
| `template/pc/view_download.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__view_download.htm.html` |
| `template/pc/view_images.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__view_images.htm.html` |
| `template/pc/view_media.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__view_media.htm.html` |
| `template/pc/view_product.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__view_product.htm.html` |
| `template/pc/view_recruit.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__view_recruit.htm.html` |
| `template/pc/view_special.htm` | enterprise_standalone_page | actual_http_passed | 普通企业官网页面或其AJAX追加片段；需要真实控制器HTTP执行。 HTTP 200；`runtime/compat/template-pages/cp058c07/pc__view_special.htm.html` |

## 限制

- HTML marker checks prove included fragment execution in successful parent responses; these fragments are not standalone endpoints.
- A passed per-file page proves the captured controller/context render only; it does not establish every parameter combination, browser interaction or all third-party official themes.
- Zero-byte original mobile enterprise templates are explicit source gaps; a separately verified runtime fallback may make their endpoints usable, but does not prove rendering of the empty mobile source. Excluded member/commerce files are not accepted as working pages.


## 修复后原包逐模板HTTP回归（2026-11-03）

`evidence/template-pages-post-compat.json` 独立保存本次marker `cp331fbc` 的46/46渲染断言；上述原清单与旧报告不改写。44企业独立页（其中2空mobile按PC fallback）、6公共片段与2个AJAX追加片段分类保持，166会员/商城文件排除。模板写入0，所有所属栏目/文档/节点/TAG/固定表单字段清理为0，模型状态恢复。

两设备资源报告 `evidence/template-page-assets-{pc,mobile}-post-compat.json` 实际重抓：PC22页71资源70成功1个原缺CUR404，mobile22页72资源全部成功；迁移失败0、incomplete=false，整体runner因CUR退出1。源页渲染全部通过不能推出全部资源或浏览器交互通过。


## 2026-11-03 运行恢复后最终原包回归

`evidence/template-pages-post-runtime.json` 46/46：44企业独立页（2空mobile以PC fallback明确标识）和2 AJAX片段；6公共片段随父页验证，166排除文件不计。所属栏目/文档/节点/TAG/固定表单字段全部0、模型状态恢复、templates_written=0。`template-page-assets-pc-post-runtime.json` 22页71资源、mobile22页72资源全部0失败，migration_failure=0、incomplete=false；这是重新渲染与资源扫描，独立于141旧URL重取。原2134资源哈希全部一致；CUR补充为同vendor替代，详见resource-recovery.md。两张远程正文图片未因此恢复，不把这套有限夹具的本地资源成功扩大为全部内容资源成功。

历史 `scalar-path-warning-fix.json` 阶段严格原版20差分17匹配，当时3个non-numeric string offset仍PHP8 TypeError、T15 open；该3项已由后续deferred有限批次修复，不能当作当前失败。真实helper/ArrayAccess warning/TypeError5项隔离通过，scoped warning回归14/14。原版暖编译依赖3差分、候选31缓存/根目录/恢复和32外部alias检查通过；实际HTTP37/37且两worker、所属夹具0，HTTP tpl_cache=false不声称暖编译。初始raw list探针用raw_observations无损归档，不推断通过；漏view_suffix/混用include输入错误与真实alias恢复500历史区分。

语言全局 `language-globals.json` 完整150/150覆盖真实原switch_language/cookie/compiler只读差分、96根部署HTTP和4双worker观察；CLI-only50是子集。单语言无合成global变量；官方lang_title/lang_url/lang_logo片段匹配。根/子目录、4 SEO/inlet组合及显式绝对URL有限通过，不证明子目录HTTP、禁用语言选择或文档级切换。

## 最新冻结实现原包回归（2026-11-03）

`evidence/template-pages-post-ast-city-stages.json`为46/46：44企业独立页（含2空mobile的PC fallback）与2AJAX；6公共片段在父模板渲染，166会员/商城模板排除。PC22页71资源、mobile22页72资源均failed0/incomplete=false，全部所属数据库fixture0、模型状态恢复、templates_written=0；原2134静态资源字节不变。此快照覆盖最终AST/static/城市实现，不是全部官方主题及浏览器交互证明。累计302份证据保留各阶段范围，不累计重叠语义。


## 其他官方主题获取核查（2026-11-04）

2026年11月4日只读查看[官方模板列表](https://www.eyoucms.com/moban/)，列表有会员模板标识；另抽样查看[企业主题31906](https://www.eyoucms.com/moban/14/31906.html)，该页虽显示0元，下载权限仍明确为“终身会员”。本次没有把0元视为开放下载，也未登录账号、购买、下载或取得新模板包。

这只是一项企业主题样本与列表入口的获取可用性观察，没有逐一核查其它主题，不能推断官方全部主题都收费或都受同一权限限制。未提供的其它主题包继续待验；本段不增加模板覆盖数、兼容率、验收通过项或阶段状态。
