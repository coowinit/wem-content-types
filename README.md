# WEM Content Types

一款轻量级 WordPress 内容结构管理插件，用于管理 **自定义文章类型（Custom Post Type）**、**自定义分类法（Taxonomy）**、重写规则以及可迁移的 JSON 配置。

> WEM Content Types 专注于解决一个问题：  
> **定义 WordPress 网站“有哪些内容，以及这些内容如何分类”。**

它不是自定义字段框架，也不是页面构建器，而是整个网站内容架构中最基础、最稳定的一层。

---

## 项目定位

在企业网站开发中，经常需要反复创建类似的内容结构：

- Product
- Solution
- Case
- FAQ
- Download
- Product Category
- Industry
- Region
- Application

传统做法通常需要在主题或插件中反复编写：

```php
register_post_type();
register_taxonomy();
```

WEM Content Types 将这部分工作独立出来，通过 WordPress 后台进行可视化管理，并支持 JSON 导入导出，便于在多个网站之间重复使用已经验证过的内容结构。

整体定位可以理解为：

```text
WEM Content Types
      ↓
定义“网站里有什么内容”

WEM Content Fields
      ↓
定义“这些内容有什么字段”

WEM Content Model
      ↓
定义“企业业务模型如何组合”

Elementor / Gutenberg / Theme
      ↓
定义“这些内容如何展示”
```

---

## 核心功能

### 自定义文章类型

支持：

- 创建 Post Type
- 编辑 Post Type
- 删除 Post Type 配置
- 启用 / 禁用 Post Type
- 自动生成常用 Labels
- Dashicons 可视化选择
- REST API / Block Editor 支持
- Archive 设置
- Supports 设置
- 高级可见性设置
- 独立 Rewrite Slug
- Permalink Front 控制

常用 Supports 包括：

- Title
- Editor
- Featured Image
- Excerpt
- Revisions
- Author
- Comments
- Page Attributes
- Custom Fields

---

### 自定义分类法

支持：

- 创建 Taxonomy
- 编辑 Taxonomy
- 删除 Taxonomy 配置
- 启用 / 禁用 Taxonomy
- 关联一个或多个 Post Type
- Hierarchical 分类式结构
- Tag 式结构
- 后台分类列显示
- REST API / Block Editor 支持
- 自动生成常用 Labels
- 独立 Rewrite Slug
- 高级可见性设置

---

### JSON 导入 / 导出

支持将完整内容结构导出为 JSON，并在其他 WordPress 网站中重新导入。

导出内容包括：

- Post Types
- Taxonomies
- Post Type / Taxonomy 关联关系
- Supports
- 可见性设置
- REST 设置
- Rewrite 配置
- Enabled / Disabled 状态

不会导出：

- Posts
- Terms
- Media
- Post Meta
- 自定义字段数据
- 页面内容

因此该功能用于：

> **内容结构迁移**

而不是：

> **网站数据库迁移**

---

## 设计原则

WEM Content Types 从一开始就坚持几个原则。

### 1. 内容结构与字段系统分离

本插件只负责：

```text
Post Types
Taxonomies
```

不会把 ACF、Meta Box 一类字段系统直接塞进核心插件。

以后自定义字段应该由独立模块负责，例如：

```text
wem-content-fields
```

这样可以避免插件越来越臃肿。

---

### 2. 内部 Slug 与前台 URL 分离

例如：

```text
Post Type Slug:
product
```

用于 WordPress 内部代码：

```php
'post_type' => 'product'
```

而前台 URL 可以设置：

```text
Rewrite Slug:
products
```

最终得到：

```text
/products/example-product/
```

这样可以同时兼顾：

- 内部代码规范
- URL 可读性
- SEO 结构
- 长期维护

---

### 3. Slug 创建后保持稳定

Post Type 和 Taxonomy 的内部 Slug 创建后不可直接修改。

例如：

```text
product
product_category
```

一旦投入使用，就应该保持稳定。

这是因为 Slug 可能已经被：

- 数据库内容
- PHP 代码
- Hooks
- REST API
- 自定义字段
- 模板文件
- 第三方插件

引用。

如果需要调整前台 URL，请修改 Rewrite Slug，而不是内部 Slug。

---

### 4. Disable 不等于 Delete

WEM Content Types 明确区分：

```text
Disable
```

和：

```text
Delete
```

禁用后：

- 不再向 WordPress 注册
- 已有内容仍然保留
- 已有 Terms 仍然保留
- Taxonomy 关联仍然保留
- 配置仍然保留

重新启用后即可恢复。

因此临时不用某个内容类型时，应优先选择：

```text
Disable
```

而不是 Delete。

---

### 5. 删除配置不会主动删除内容

删除 Post Type 或 Taxonomy 配置时，插件不会自动删除：

- 文章
- Terms
- Post Meta
- 媒体
- 其他内容数据

这是为了避免误操作造成不可逆的数据丢失。

---

### 6. Taxonomy 依赖保护

如果某个 Taxonomy 仍然关联某个 Post Type：

```text
product_category
    ↓
product
```

则不能直接删除：

```text
product
```

需要先解除 Taxonomy 关联。

这样可以避免产生悬空配置。

---

## 推荐使用方式

### Product

推荐配置：

```text
Post Type Slug:
product

Singular Label:
Product

Plural Label:
Products

Public:
✓

Archive:
✓

REST API / Block Editor:
✓

Supports:
✓ Title
✓ Editor
✓ Featured Image
✓ Excerpt
✓ Revisions

Rewrite URLs:
✓

Rewrite Slug:
products

Use Permalink Front:
□
```

这样内部使用：

```text
product
```

前台 URL：

```text
/products/example-product/
```

---

### Product Category

推荐配置：

```text
Taxonomy Slug:
product_category

Singular Label:
Product Category

Plural Label:
Product Categories

Attach To:
✓ Products

Public:
✓

Hierarchical:
✓

Admin Column:
✓

REST API / Block Editor:
✓

Rewrite URLs:
✓

Rewrite Slug:
product-category

Use Permalink Front:
□
```

内部代码仍然使用：

```php
get_the_terms( $post_id, 'product_category' );
```

前台可以使用：

```text
/product-category/decking/
```

---

## 安装

1. 下载插件 ZIP 文件
2. 登录 WordPress 后台
3. 进入：

```text
插件 → 安装插件 → 上传插件
```

4. 上传：

```text
wem-content-types.zip
```

5. 安装并启用

启用后后台会出现：

```text
WEM Content Types
├── Post Types
├── Taxonomies
└── Tools
```

---

## 创建 Post Type

进入：

```text
WEM Content Types
→ Post Types
→ Add Post Type
```

基础设置包括：

```text
Post Type Slug
Menu Icon
Singular Label
Plural Label
Description
```

Behavior 包括：

```text
Public
Archive
REST API / Block Editor
```

Supports 包括：

```text
Title
Editor
Featured Image
Excerpt
Revisions
Author
Comments
Page Attributes
Custom Fields
```

低频参数放在：

```text
Advanced Settings
```

中，避免普通配置页面过于复杂。

---

## 创建 Taxonomy

进入：

```text
WEM Content Types
→ Taxonomies
→ Add Taxonomy
```

基础设置包括：

```text
Taxonomy Slug
Singular Label
Plural Label
Description
Attach To
```

Behavior 包括：

```text
Public
Hierarchical
Admin Column
REST API / Block Editor
```

Taxonomy 可以关联：

- WEM 创建的 Post Type
- WordPress Posts / Pages
- 其他插件或主题注册的公开 Post Type

WordPress 内部 Post Type 会默认过滤，不显示在普通候选列表中。

---

## Rewrite 设置

Rewrite 控制的是：

> **前台 URL**

而不是：

> **内部内容类型标识**

例如：

```text
Post Type Slug:
product
```

可以配合：

```text
Rewrite Slug:
products
```

形成：

```text
/products/product-name/
```

### 注意

正式网站上线后，不建议随意修改 Rewrite Slug。

如果必须修改，例如：

```text
/product/example/
```

改为：

```text
/products/example/
```

建议同步配置：

```text
301 Redirect
```

避免旧 URL 失效。

---

## JSON 导出

进入：

```text
WEM Content Types
→ Tools
→ Export Structure
```

插件会生成类似：

```text
wem-content-types-2026-09-15.json
```

JSON 中会保存当前网站的内容结构配置。

适合用于：

- 新网站初始化
- 企业网站模板复制
- 多站点结构统一
- 内容架构备份
- 开发 / 测试 / 生产环境迁移

---

## JSON 导入

进入：

```text
WEM Content Types
→ Tools
→ Import Structure
```

导入采用：

> **安全合并**

规则如下：

```text
JSON 中的新 Slug
→ 新增

JSON 中已经存在的 WEM Slug
→ 更新

目标网站中其他 WEM 配置
→ 保留
```

插件不会因为导入文件而自动删除目标网站中其他配置。

---

## 导入安全机制

导入不是逐条直接写入，而是：

```text
读取 JSON
↓
检查文件格式
↓
检查 Schema
↓
验证所有 Post Types
↓
验证所有 Taxonomies
↓
检查关联关系
↓
检查 Slug 冲突
↓
全部通过
↓
执行导入
```

如果其中任何一项失败：

```text
整个导入取消
```

避免出现部分数据已经写入、部分失败的半完成状态。

---

## JSON Schema

导出文件包含：

```json
{
  "format": "wem-content-types",
  "schema_version": 1,
  "plugin_version": "1.0.0"
}
```

其中：

```text
schema_version
```

代表 JSON 数据结构版本。

```text
plugin_version
```

代表插件版本。

二者独立。

这样以后即使插件升级，只要 JSON Schema 没有变化，旧配置仍然可以继续导入。

---

## 数据存储

当前配置使用 WordPress Options 保存：

```text
wem_ct_post_types
wem_ct_taxonomies
```

插件运行时读取这些配置，并调用：

```php
register_post_type();
register_taxonomy();
```

完成实际注册。

这种架构保持了较低复杂度，也方便长期维护和跨站迁移。

---

## 注册顺序

核心注册顺序为：

```text
init priority 5
↓
Post Types

init priority 6
↓
Taxonomies

init priority 99
↓
按需刷新 Rewrite Rules
```

Rewrite Rules 不会在每次请求时刷新。

只有配置发生变化时才会执行必要刷新。

---

## 扩展 Hooks

WEM Content Types 从 v1.0.0 开始提供稳定的扩展接口。

### Post Type Filters

```text
wem_ct_post_type_args
wem_ct_post_type_labels
wem_ct_should_register_post_type
```

### Post Type Actions

```text
wem_ct_before_register_post_types
wem_ct_before_register_post_type
wem_ct_post_type_registered
wem_ct_after_register_post_types
```

### Taxonomy Filters

```text
wem_ct_taxonomy_args
wem_ct_taxonomy_labels
wem_ct_taxonomy_object_types
wem_ct_taxonomy_available_post_types
wem_ct_should_register_taxonomy
```

### Taxonomy Actions

```text
wem_ct_before_register_taxonomies
wem_ct_before_register_taxonomy
wem_ct_taxonomy_registered
wem_ct_after_register_taxonomies
```

### Import / Export

```text
wem_ct_export_payload
wem_ct_before_import
wem_ct_after_import
```

### 权限

```text
wem_ct_manage_capability
```

未来如果需要允许 Editor 或自定义角色管理 WEM Content Types，可以通过该 Filter 扩展，而不需要修改插件核心。

---

## 二次开发

WEM Content Types 本身不会直接实现完整自定义字段系统。

推荐的扩展方式是：

```text
WEM Content Types
        ↓
Post Types / Taxonomies

WEM Content Fields
        ↓
Custom Fields / Field Groups

WEM Content Model
        ↓
Product / Solution / FAQ / Download 模型
```

字段模块可以通过 WordPress 原生 API：

```php
register_post_meta();
add_meta_box();
get_post_meta();
update_post_meta();
```

为任意 WEM Post Type 增加字段。

例如：

```php
register_post_meta(
    'product',
    'wem_product_badge',
    [
        'type'              => 'string',
        'single'            => true,
        'show_in_rest'      => true,
        'sanitize_callback' => 'sanitize_text_field',
    ]
);
```

这样无需修改 WEM Content Types 核心代码。

---

## 与 ACF / Meta Box 等插件共存

WEM Content Types 不限制字段系统。

可以自由组合：

```text
WEM Content Types
+
ACF
```

或者：

```text
WEM Content Types
+
Meta Box
```

也可以未来使用：

```text
WEM Content Types
+
WEM Content Fields
```

内容类型和字段层保持解耦。

---

## 企业网站中的典型结构

例如一个 B2B 企业网站可以定义：

```text
Product
├── Product Category
├── Industry
└── Application

Solution
├── Region
└── Industry

Case
├── Industry
└── Region

FAQ
└── FAQ Category

Download
└── Download Category
```

WEM Content Types 负责建立这些结构。

字段信息，例如：

```text
Badge
Dimensions
Material
Images
PDF
Video
```

应由字段层负责。

---

## 为什么不直接把所有功能做进一个插件？

如果把以下能力全部放进一个插件：

```text
CPT
Taxonomy
Custom Fields
Repeater
Relationship
Templates
Blocks
SEO
AI
```

插件会迅速变得复杂。

WEM Content Types 更强调：

> **小核心 + 稳定边界 + 可扩展**

因此 v1.0.0 只专注于：

```text
Post Types
Taxonomies
Rewrite
Status
Import / Export
Hooks
```

这是经过实际企业网站开发后更适合长期维护的结构。

---

## 安全设计

当前版本已经处理：

- Nonce 验证
- 用户权限检查
- Slug 格式验证
- Post Type 最长 20 字符
- Taxonomy 最长 32 字符
- 保留 Slug / 已存在对象冲突检查
- Taxonomy 依赖保护
- 禁用状态数据保护
- 第三方 CPT 临时不可用时保留原关联
- JSON 格式验证
- JSON Schema 验证
- 完整导入预检查
- Import Safe Merge
- Rewrite Rules 按需刷新

---

## Slug 命名建议

推荐使用小写英文。

Post Type：

```text
product
solution
case
faq
download
```

Taxonomy：

```text
product_category
solution_region
faq_category
industry
application
```

不要使用：

```text
Product
Product Category
产品分类
```

作为内部 Slug。

内部 Slug 应保持：

- 稳定
- 简洁
- 可读
- 适合代码使用

---

## 版本说明

### v1.0.0

首个稳定版本。

完成：

- Post Type CRUD
- Taxonomy CRUD
- Post Type / Taxonomy 关联
- Enable / Disable
- 自动 Labels
- Dashicons 选择器
- Basic / Advanced 设置
- Rewrite Controls
- REST API / Block Editor
- JSON Import / Export
- Safe Merge
- Taxonomy 依赖保护
- 第三方 CPT 关联保护
- 扩展 Hooks
- README 与正式发布文档

v1.0.0 兼容此前 v0.x 开发版本保存的配置，无需手动迁移数据。

---

## 后续方向

WEM Content Types v1.0.0 已经完成内容结构层。

后续不会为了增加功能而不断扩大核心插件。

未来更适合通过独立模块继续扩展：

```text
wem-content-fields
```

用于：

- Field Groups
- Text
- Textarea
- Number
- URL
- Select
- Checkbox
- Image
- File

进一步再由：

```text
wem-content-model
```

提供企业网站预设模型，例如：

- Product Model
- Solution Model
- FAQ Model
- Download Model

最终形成：

```text
WEM WordPress Content Framework
│
├── WEM Content Types
│   ├── Post Types
│   └── Taxonomies
│
├── WEM Content Fields
│   ├── Field Groups
│   └── Custom Fields
│
├── WEM Content Model
│   ├── Product
│   ├── Solution
│   ├── FAQ
│   └── Download
│
└── Presentation
    ├── Elementor
    ├── Gutenberg
    └── Theme Templates
```

---

## 许可证

本项目采用 GPL-2.0-or-later 许可证。

---

## 项目理念

WEM Content Types 不追求成为一个“大而全”的 WordPress 框架。

它更关注：

```text
简单
稳定
可维护
可迁移
可扩展
```

先把网站最基础的内容结构定义清楚，再让字段、业务模型和页面展示建立在这个稳定基础之上。

> **先建立结构，再扩展能力。**
