<!-- keepeek field type -->

<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')
    <div class="input-group">
        <input type="text"
                name="{{ $field['name'] }}"
                value="{{ old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '' }}"
                data-javascript-function-for-field-initialisation="bpFieldInitKeepeek"
                class="form-control">
        <span class="input-group-btn">
            <button type="button" class="btn btn-primary" data-toggle="modal">
                    {!! $field['label_button'] !!}
            </button>
        </span>
    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    @push('before_scripts')
    <div class="modal fade keepeek__modal" id="kpModal" tabindex="-1" role="dialog" aria-labelledby="keepeek-modal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="modal-title">{!! $field['label'] !!}</h4>
                    </div>
                    <div class="col-md-4">
                            <div class="dropdown pull-right">
                                <button id="dLabel" class="btn btn-primary" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Tags
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu keepeek_labels" aria-labelledby="dLabel">

                                </ul>
                            </div>
                    </div>
                </div>
            </div>
            <div class="modal-body box">
                <div class="overlay keepeek__overlay" style="display:none">
                    <i class="fa fa-refresh fa-spin"></i>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <ul class="keepeek__folders tree">

                        </ul>
                    </div>
                    <div class="col-md-8 keepeek_images__container">
                        <div class="row">
                            <div class="col-md-12 keepeek_images">
                            </div>
                            <div class="col-md-12 text-right">
                                <div id="page-selection"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('backpack::base.cancel') }}</button>
            </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    @endpush

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
    <style>
        .keepeek__label__wrapper {
            width: 100%;
            padding: 5px 10px;
        }

        .keepeek__label .checkbox {
            margin:0px;
            padding-left:10px;
        }

        .keepeek__image__wrapper {
            overflow: hidden;
            text-overflow: ellipsis;
            height:165px;
        }

        .keepeek_image_container {
            height:110px;
            overflow:hidden;
        }

        .keepeek__image__title {
            display: inline-block;
            height: 45px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .keepeek__folders {
            list-style: none;
            padding-left:0px
        }

        .keepeek__folder__number {
            background: #eee;
            color: #000;
            border-radius: 10px;
            padding: 2px 5px;
            display: inline-block;
            float: right;
        }

        .keepeek__folder {
            margin:5px 0px;
        }

        .keepeek_images__container {
            border-left: 1px solid #ddd;
            min-height: 1000px;
        }

        .keepeek__modal .box {
            margin-bottom:0px;
            border:none;
        }

        #kpModal {
            z-index: 99999;
        }
    </style>
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <!-- include keepeek needed js-->
    <script src="{{ asset('keepeek/simple-bootstrap-paginator.min.js') }}"></script>

    <script id="folder-template" type="html/template">
        <li class="keepeek__folder treeview" data-id="" data-is-leaf="0"><a href="#"><span class="keepeek__folder__title"></span> <span class="keepeek__folder__number"></span><div class="clearfix"></div></a></li>
    </script>

    <script id="media-template" type="html/template">
        <div class="col-md-3 col-sm-6 text-center" >
            <a href="#" class="thumbnail keepeek__image__wrapper">
                <div class="keepeek_image_container">
                    <img src="" class="keepeek__image img-responsive" id="" path="" alt="">
                </div>
                <span class="keepeek__image__title"></span>
            </a>
        </div>
    </script>

    <script id="label-template" type="html/template">
        <li class="keepeek__label ">
            <div class="checkbox">
                <label class="keepeek__label__wrapper">
                    <input name ="" type="checkbox" class="keepeek__label__checkbox" > <span class="keepeek__label__name"></span>
                </label>
            </div>
        </li>
    </script>
    <script>
        /*
        * The constructor for keepeek modal, initial API calls and default parameters.
        */
        function KeepeekModal ($modalDomElement) {
            this.modalDomElement = $modalDomElement;
            this.currentFolder = null;
            this.labels = [];
            this.page = 1;
            this.selectedLabels = [];
            this.pagination = null;
            this.loadingsOpenned = 0;
            this.itemsOnPage = 20;
            this.triggerElement = null;

            if (this.modalDomElement.find(".pagination").length == 0) {
                this.initPagination();
            } else {
                this.pagination = this.modalDomElement.find('#page-selection');
            }
            this.getChildrenRootFolder();
            this.getLabels();
            this.getMedias();
        }

        /**
        * Check if the loading should be shown and show it
        */
        KeepeekModal.prototype.showLoading = function () {
            if (this.loadingsOpenned == 0) {
                this.modalDomElement.find(".keepeek__overlay").show();
            }
            this.loadingsOpenned += 1;
        }

        /**
        * Check if the loading should be hidden and hide it
        */
        KeepeekModal.prototype.hideLoading = function () {
            this.loadingsOpenned -= 1;
            if (this.loadingsOpenned == 0) {
                this.modalDomElement.find(".keepeek__overlay").hide();
            }
        }

        /**
            * It populates the template of a folder with received values and returns the resulted jquery object
            *
            * @param keepeekObj object returned by API for a folder
            *
            * @return The jquery object of the folder
        */
        KeepeekModal.prototype.getHtmlFolder = function (keepeekObj) {
            let folder = $($('#folder-template').html());

            folder.find('a .keepeek__folder__title').text(keepeekObj.title);
            folder.find('a .keepeek__folder__number').text(keepeekObj.treeMediaCount);
            folder.attr('data-id', keepeekObj.id);

            return folder;
        }

        /**
            Get the children folders of the root(Call the folder-tree route without parameters)
        */
        KeepeekModal.prototype.getChildrenRootFolder = function () {
            let that  = this;
            that.modalDomElement.find(" .keepeek__folders").first().html("");
            that.showLoading();
            $.ajax({
                url: "{{ $field['url_controller'] }}",
                type: 'GET',
                dataType: 'json',
                data: {
                    'keepeek_url': "api/dam/folder-tree"
                },
                success: function (response) {
                    if (Array.isArray(response._embedded.child)) {
                        $.each(response._embedded.child, function (index, folder) {
                            that.modalDomElement.find(" .keepeek__folders").first().append(that.getHtmlFolder(folder));
                        });
                    } else {
                        that.modalDomElement.find(" .keepeek__folders").first().append(that.getHtmlFolder(response._embedded.child));
                    }

                    that.hideLoading();
                },
                error: function (response) {
                    that.hideLoading();
                    console.log(response);
                }
            });
        }

        /**
        *    Get the children folders for the folder received as parameter
        *
        *    @param idFolder for whom is required the children
        */
        KeepeekModal.prototype.getChildrensFolder = function (idFolder) {
            let that  = this;
            that.showLoading();
            $.ajax({
                url: "{{ $field['url_controller'] }}",
                type: 'GET',
                dataType: 'json',
                data: {
                    'keepeek_url': "api/dam/folder-tree/" + idFolder
                },
                success: function (response) {
                    that.modalDomElement.find(" li[data-id=" + idFolder + "]").addClass("loaded");
                    if (response.childrenCount != 0) {
                        that.modalDomElement.find(" li[data-id=" + idFolder + "]").append("<ul class='treeview-menu' style='display:block'></ul>");
                        that.modalDomElement.find(" li[data-id=" + idFolder + "]").addClass("menu-open");
                        if (Array.isArray(response._embedded.child)) {
                            $.each(response._embedded.child, function (index, folder) {
                                that.modalDomElement.find(" li[data-id=" + idFolder + "] ul").append(that.getHtmlFolder(folder));
                            });
                        } else {
                            that.modalDomElement.find(" li[data-id=" + idFolder + "] ul").append(that.getHtmlFolder(response._embedded.child));
                        }
                    }
                    that.hideLoading();
                },
                error: function (response) {
                    that.hideLoading();
                    console.log(response);
                }
            });
        }

        /**
            * It populates the template of a label with received values and returns the resulted jquery object
            *
            * @param keepeekLabelObj object returned by API
            *
            * @return The jquery object of the label
        */
        KeepeekModal.prototype.getHtmlLabel = function (keepeekLabelObj) {
            let label = $($('#label-template').html());

            label.find('.keepeek__label__name').text(keepeekLabelObj.title);
            label.attr('data-id', keepeekLabelObj.id);
            label.find('.keepeek__label__checkbox').val(keepeekLabelObj.id);
            label.css('background-color', keepeekLabelObj.backgroundColor);
            label.css('color', keepeekLabelObj.textColor);

            return label;
        }

        /**
            Get the labels and populate the dropdown of the labels
        */
        KeepeekModal.prototype.getLabels = function () {
            let that = this;
            that.showLoading();
            that.modalDomElement.find(" .keepeek_labels").html("");
            $.ajax({
                url: "{{ $field['url_controller'] }}",
                type: 'GET',
                dataType: 'json',
                data: {
                    'keepeek_url': "api/dam/medias/labels"
                },
                success: function (response) {
                    if (Array.isArray(response._embedded.label)) {
                        $.each(response._embedded.label, function (index, label) {
                            that.modalDomElement.find(" .keepeek_labels").append(that.getHtmlLabel(label));
                        });
                    } else {
                        that.modalDomElement.find(" .keepeek_labels").append(that.getHtmlLabel(response._embedded.label));
                    }

                    that.hideLoading();
                },
                error: function (response) {
                    that.hideLoading();
                    console.log(response);
                }
            });
        }

        /**
        * Update internal array with checked labels
        */
        KeepeekModal.prototype.updateLabelsArray = function () {
            let that = this;
            this.labels = [];
            $('.keepeek__label__checkbox').each(function () {
                    if ($(this).is(':checked')) {
                        that.labels.push($(this).val());
                    }
            });
        }

        /**
            * Return the jquery object of a media. Get the media template and populate it with received data
            *
            * @param keepeekMediaObj object returned by API
            *
            * @return The jquery object of the media
        */
        KeepeekModal.prototype.getHtmlMedia = function (keepeekMediaObj) {
            let media = $($('#media-template').html());
            let sizes = eval("(" + '{!! json_encode($field["sizes"]) !!}' + ")");

            media.find('.keepeek__image').attr('src', keepeekMediaObj._links['kpk:medium'].href);
            media.find('.keepeek__image').attr('data-id', keepeekMediaObj.id);
            media.find('.keepeek__image__title').text(keepeekMediaObj.title);
            media.find('.keepeek__image').attr('alt', keepeekMediaObj.title);
            media.find('.keepeek__image__wrapper').attr('title', keepeekMediaObj.title);

            for (var type in sizes) {
              if (
                keepeekMediaObj.mediaType.startsWith(type) &&
                keepeekMediaObj._links[sizes[type]] != null
                ) {
                    media.find('.keepeek__image').attr('data-path', keepeekMediaObj._links[sizes[type]].href);
                    return media;
              }
            }

            media.find('.keepeek__image').attr('data-path', keepeekMediaObj._links['preview'].href);
            return media;
        }

        /**
            * Get the medias by API and populate right side with them
        */
        KeepeekModal.prototype.getMedias = function () {
            let that = this;
            that.showLoading();
            that.modalDomElement.find(" .keepeek_images").html("");

            let dataObj = {};
            dataObj.keepeek_url = "api/dam/search/media";

            // create the filters string, it can contains folder id and/or labels ids
            let fqString = "";
            if (this.currentFolder != null) {
                fqString = "folderId:" + this.currentFolder + " subtree";
            }

            if (this.labels.length != 0) {
                if (fqString != "") {
                    fqString = fqString + "&fq=";
                }

                fqString = fqString + "labelId:(" + that.labels.join() + ")";
            }

            if (fqString != "") {
                dataObj.fq = fqString;
            }

            //add items per page and current page in sent object
            dataObj.size = that.itemsOnPage;
            dataObj.page = that.page;
            dataObj.sort = "updateDate desc";

            $.ajax({
                url: "{{ $field['url_controller'] }}",
                type: 'GET',
                dataType: 'json',
                data: dataObj,
                success: function (response) {
                    that.modalDomElement.find(" .keepeek_images").html("");
                    if (response.totalCount == 0) {
                        that.modalDomElement.find(" .keepeek_images").append('<p style="text-align:center">There are no items for your criteria!</p>');
                        that.hideLoading();
                        if (that.page == 1) {
                            that.modalDomElement.find(" .pagination").hide();
                        }
                        return;
                    }

                    //calculate max page, the api is limited at 10000 results
                    let numberItems = response.totalCount;
                    let extraPage = 0;
                    if (numberItems > 10000) {
                        numberItems = 10000;
                    }

                    if (numberItems % that.itemsOnPage != 0) {
                        extraPage = 1;
                    }

                    that.pagination.simplePaginator('setTotalPages', parseInt(numberItems / that.itemsOnPage) + extraPage);
                    that.modalDomElement.find(" .pagination").show();

                    //populate with medias, if is more than one element returned by api, there is an array otherwise there is a object
                    if (Array.isArray(response._embedded.media)) {
                        $.each(response._embedded.media, function (index, media) {
                            if (index % 4 == 0) {
                                that.modalDomElement.find(" .keepeek_images").append("<div class='clearfix'></div>")
                            }
                            that.modalDomElement.find(" .keepeek_images").append(that.getHtmlMedia(media));
                        });
                    } else {
                        that.modalDomElement.find(" .keepeek_images").append(that.getHtmlMedia(response._embedded.media));
                    }

                    that.hideLoading();
                },
                error: function (response) {
                    that.hideLoading();
                    console.log(response);
                }
            });
        }

        /**
            * Initialization of the pagination, set call back function for page click and others options
        */
        KeepeekModal.prototype.initPagination = function () {
            let that = this;
            this.pagination = this.modalDomElement.find('#page-selection').simplePaginator({
                totalPages: 1,
                currentPage: 1,
                maxButtonsVisible: 4,
                firstLabel: '←',
                lastLabel: '→',
                pageChange: function(page) {
                    let oldPage = that.page;
                    that.page = page;
                    if (page != oldPage) {
                        that.getMedias();
                    }
                }
            });
        }

        /**
            * Reset the pagination setting page to 1
        */
        KeepeekModal.prototype.resetPagination = function () {
            this.page = 1;
            this.pagination.simplePaginator('changePage', 1);
        };
    </script>

    <script>
        let keepeekModalInstance = null;

        jQuery(document).ready(function($) {
            let modalDomElement = $('#kpModal');
            $('.keepeek__folders').tree();

            //get the children of the clicked folder
            modalDomElement.on('click', '.keepeek__folder', function (event) {
                event.stopPropagation();
                event.preventDefault();

                $(this).find('a').removeClass('text-bold');
                $(this).find('a').first().addClass('text-bold');
                let idFolder = $(this).attr('data-id');
                keepeekModalInstance.currentFolder = idFolder;
                keepeekModalInstance.resetPagination();
                keepeekModalInstance.getMedias();

                if (!$(this).hasClass('loaded')) {
                    keepeekModalInstance.getChildrensFolder(idFolder);
                }
            });

            // Get medias when a new label is checked/unchecked
            modalDomElement.on('change', '.keepeek__label__checkbox', function (event) {
                keepeekModalInstance.updateLabelsArray();
                keepeekModalInstance.resetPagination();
                keepeekModalInstance.getMedias();
            });

            // Populate text input with required url
            modalDomElement.on('click', '.keepeek__image__wrapper', function (event) {
                keepeekModalInstance.triggerElement.find('input').val($(this).find('img').attr('data-path'));
                modalDomElement.modal('hide');
            });
        });

        function bpFieldInitKeepeek(element) {
            let modalDomElement = $('#kpModal');

            // Get data from keepeek when the modal is openned
            element.parent().on('click', 'button', function (event) {
                keepeekModalInstance = new KeepeekModal(modalDomElement);
                modalDomElement.modal('show');
                keepeekModalInstance.triggerElement = $(this).parent().parent(); // the .input-group that contains both the button and the input
            });
        }
    </script>

    @endpush

@endif
