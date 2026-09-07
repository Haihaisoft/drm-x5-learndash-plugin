# DRM-X 5.0 LearnDash Plugin - WordPress Video DRM and Course Protection

[![DRM-X 5.0](https://img.shields.io/badge/DRM--X-5.0-155EEF)](https://www.drm-x.com/en/products/drm-x-5.0/features)
[![Plugin version](https://img.shields.io/badge/plugin-v1.0.0-2E8B57)](./drmx5-learndash.php)
[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-21759B)](https://wordpress.org/)
[![LearnDash](https://img.shields.io/badge/LMS-LearnDash-5B2D90)](https://www.learndash.com/)

**DRM-X 5.0 Integration for LearnDash** connects WordPress and LearnDash course access with DRM-X digital rights management. Authorized learners can obtain licenses and play encrypted training videos through the ZJGet secure browser, while users without current LearnDash course access are rejected.

This WordPress LMS DRM plugin is designed for paid online courses, membership learning, professional certification, and corporate training that need video encryption, enrollment-based license delivery, watermarking, license revocation, and stronger anti-piracy controls.

> [Jump to the Chinese introduction](#中文简介).

## Why protect LearnDash videos with DRM-X?

LearnDash controls access to WordPress course pages. DRM-X 5.0 adds encrypted media and license-based playback, helping protect copied or downloaded course files outside the normal page-access workflow. DRM-X policies can also provide dynamic watermarks, device controls, license revocation, virtual-machine restrictions, and screen-recording protection according to the selected DRM-X account and Rights settings.

Learn more about [DRM-X 5.0 digital content protection](https://www.drm-x.com/en/products/drm-x-5.0/features) and its [security architecture](https://www.drm-x.com/en/products/drm-x-5.0/features/new-drm-security-architecture).

## Features

- Requires WordPress sign-in before issuing a DRM-X license.
- Uses LearnDash's course-access result, including direct enrollment and LearnDash Group access when LearnDash reports that the learner can access the course.
- Accepts one or multiple course post IDs, for example `123-124-208`.
- Automatically creates the matching DRM-X user when necessary.
- Blocks existing DRM-X users who are revoked or locked.
- Supports DRM-X 5.0 International (`5.drm-x.com`) and China (`5.drm-x.cn`).
- Supports License Profile RightsID from ZJGet POST data without changing DRM-X Rights.
- Supports a fixed RightsID and calls `UpdateRightWithDisableVirtualMachine` before licensing.
- Embeds protected video using `[zjget-player]URL[/zjget-player]`.
- Disables the player in WordPress editing and preview contexts.
- Includes English and Simplified Chinese interface text.

## Requirements

- WordPress 5.8 or later
- PHP 7.4 or later with the SOAP extension enabled
- LearnDash installed and activated
- HTTPS enabled on the WordPress website
- A DRM-X 5.0 account, License Profile, Rights, and user group
- ZJGet secure browser for DRM-X 5.0 protected content

## Download and documentation

- [Download DRM-X 5.0 LearnDash Plugin v1.0.0](https://github.com/Haihaisoft/drm-x5-learndash-plugin/raw/refs/heads/main/dist/drmx5-learndash-1.0.0.zip)
- [English PDF User Guide](https://github.com/Haihaisoft/drm-x5-learndash-plugin/raw/refs/heads/main/docs/DRM-X-5.0-learndash-integration-user-guide-v1.0.0.pdf)
- [中文 PDF 使用指南](https://github.com/Haihaisoft/drm-x5-learndash-plugin/raw/refs/heads/main/docs/DRM-X-5.0-learndash-integration-user-guide-zh-CN-v1.0.0.pdf)

## Installation

1. Install and activate LearnDash.
2. In WordPress, open **Plugins > Add New Plugin > Upload Plugin**.
3. Upload `drmx5-learndash-1.0.0.zip` and activate it.
4. Open **Settings > DRM-X 5.0**.

For manual installation, copy the plugin to:

```text
wp-content/plugins/drmx5-learndash/
```

## Plugin settings

| Setting | Description |
| --- | --- |
| `AdminEmail` | Administrator email address of the DRM-X 5.0 account. |
| `WebServiceAuthStr` | Web Service Authorization String configured in DRM-X 5.0. Keep it secret. |
| `GroupID` | DRM-X user group used for user creation and license requests. |
| Service area | International uses `5.drm-x.com`; China uses `5.drm-x.cn`. |
| RightsID mode | Read the License Profile RightsID from POST, or use a fixed RightsID and update it automatically. |
| Fixed RightsID | Required only in fixed mode. |

### RightsID modes

**Read RightsID from ZJGet POST data:** ZJGet submits the RightsID configured through the DRM-X License Profile. The plugin uses it for license delivery and does not update the DRM-X permission.

**Fixed RightsID and automatic update:** the plugin calls `UpdateRightWithDisableVirtualMachine` before every license request. It uses LearnDash course-access start and expiration timestamps when available; missing values fall back to the request time and an expiration ten years later. `ExpirationAfterFirstUse` is `-1`.

## DRM-X License Profile setup

In **DRM-X 5.0 Account Settings > Website Integration Settings**, enter the same `WebServiceAuthStr` and set the custom integration URL to:

```text
https://your-wordpress-domain.example/wp-content/plugins/drmx5-learndash/licstore5.php
```

The endpoint filename must remain `licstore5.php`, and the hostname must match the HTTPS hostname used for WordPress sign-in.

### Course ID

Edit the LearnDash course. If the WordPress URL contains:

```text
/wp-admin/post.php?post=123&action=edit
```

enter `123` in the License Profile **Your Product ID** field. Use the numeric LearnDash course post ID, not a lesson ID, slug, product ID, or URL.

Multiple courses use a standard hyphen:

```text
123-124-208
```

The plugin checks from left to right and uses the first course the learner can access. Access to one listed course does not grant access to another course; the list applies only to the current License Profile request.

## Embed a protected video

Add a WordPress **Shortcode** block to the LearnDash lesson or course content:

```text
[zjget-player]https://cdn.example.com/course/protected-video.mp4[/zjget-player]
```

- Use a complete URL beginning with `http://` or `https://`.
- Put only the raw URL inside the shortcode; do not use Markdown link syntax.
- Use one ZJGet player on the same page.
- Player files load from `www.zjget.com` and do not need to be copied locally.
- Editing and preview contexts show a placeholder instead of the player overlay.

## License workflow

1. Save an incoming DRM-X request and require WordPress sign-in.
2. Read `YourProductID` and select the first accessible LearnDash course.
3. Create the DRM-X user when the WordPress username does not exist there.
4. Call `CheckUserIsRevoked` and `GetUserDetails` for existing DRM-X users.
5. Apply the selected RightsID mode.
6. Request the DRM-X license and return it to ZJGet.

The same authorization check applies when a downloaded encrypted file requests a license. This plugin validates course-level access, not a restriction applied only to an individual lesson or page.

## Troubleshooting

| Problem | Check |
| --- | --- |
| License page asks the learner to sign in again | Use the same HTTPS domain for WordPress sign-in and `licstore5.php`. |
| Browser warns that submitted information is insecure | Enable HTTPS for both WordPress and the DRM-X integration URL. |
| Learner is reported as unauthorized | Confirm `YourProductID` contains the numeric LearnDash course post ID and verify LearnDash access. |
| Shortcode appears instead of the player | Confirm LearnDash and this integration plugin are active and use a Shortcode block. |
| Player reports an invalid URL | Put a complete raw HTTP or HTTPS URL inside the shortcode. |
| WordPress cannot connect to DRM-X | Enable PHP SOAP and verify the credentials and service area. |

## Privacy and security

During DRM-X user creation or licensing, the plugin may send the WordPress username, email, display name, IP address, and DRM client/device request data to the selected DRM-X service. Review applicable privacy requirements before deployment. Always use HTTPS and never publish `WebServiceAuthStr` or real account credentials.

## 中文简介

**DRM-X 5.0 LearnDash 集成插件**用于连接 WordPress、LearnDash、DRM-X 5.0 和 ZJGet 播放器，根据学员当前的 LearnDash 课程访问权限签发加密视频许可证。

主要功能：

- 必须登录 WordPress 并拥有 LearnDash 课程权限。
- 支持直接加入课程以及 LearnDash Group 授予的有效访问权限。
- 支持 `123-124-208` 格式的多课程 ID。
- DRM-X 用户不存在时自动创建，被吊销或锁定时拒绝许可证。
- 支持中国版、国际版和两种 RightsID 模式。
- 使用 `[zjget-player]URL[/zjget-player]` 嵌入加密视频。

安装后进入 **设置 > DRM-X 5.0**，填写 `AdminEmail`、`WebServiceAuthStr`、`GroupID`、服务区域和 RightsID 模式。然后在 DRM-X 5.0 许可证模板的 **Your Product ID** 中填写 LearnDash 课程文章 ID。

## Support

- [DRM-X 5.0 product overview](https://www.drm-x.com/en)
- [DRM-X 5.0 features](https://www.drm-x.com/en/products/drm-x-5.0/features)
- [Haihaisoft](https://www.haihaisoft.com/)

Developed and maintained by [Haihaisoft](https://www.haihaisoft.com/).
