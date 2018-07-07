<?php

namespace Backpack\CRUD\PanelTraits;

trait Views
{
    protected $createView = 'create';
    protected $editView = 'edit';
    protected $showView = 'show';
    protected $detailsRowView = 'details_row';
    protected $revisionsView = 'revisions';
    protected $revisionsTimelineView = 'inc.revision_timeline';
    protected $reorderView = 'reorder';
    protected $listView = 'list';

    // -------
    // CREATE
    // -------

    /**
     * Sets the list template.
     * @param string $view name of the template file
     * @return string $view name of the template file
     */
    public function setCreateView($view)
    {
        $this->createView = $view;

        return $this->createView;
    }

    /**
     * Gets the create template.
     * @return string name of the template file
     */
    public function getCreateView()
    {
      // if the view exists return it
      if(view()->exists($this->listView)) {
        return $this->createView;
      }

      // otherwise return the default view
      return backpack_view('create', 'crud');
    }

    // -------
    // READ
    // -------

    /**
     * Sets the list template.
     * @param string $view name of the template file
     * @return string $view name of the template file
     */
    public function setListView($view)
    {
        $this->listView = $view;

        return $this->listView;
    }

    /**
     * Gets the list template.
     * @return string name of the template file
     */
    public function getListView()
    {
        // if the view exists return it
        if(view()->exists($this->listView)) {
          return $this->listView;
        }

        // otherwise return the default view
        return backpack_view('list', 'crud');

    }

    /**
     * Sets the details row template.
     * @param string $view name of the template file
     * @return string $view name of the template file
     */
    public function setDetailsRowView($view)
    {
        $this->detailsRowView = $view;

        return $this->detailsRowView;
    }

    /**
     * Gets the details row template.
     * @return string name of the template file
     */
    public function getDetailsRowView()
    {
        // if the view exists return it
        if(view()->exists($this->detailsRowView)) {
          return $this->detailsRowView;
        }

        // otherwise return the default view
        return backpack_view('details_row', 'crud');
    }

    /**
     * Sets the show template.
     * @param string $view name of the template file
     * @return string $view name of the template file
     */
    public function setShowView($view)
    {
        $this->showView = $view;

        return $this->showView;
    }

    /**
     * Gets the show template.
     * @return string name of the template file
     */
    public function getShowView()
    {
        // if the view exists return it
        if(view()->exists($this->showView)) {
          return $this->showView;
        }

        // otherwise return the default view
        return backpack_view('show', 'crud');
    }

    // -------
    // UPDATE
    // -------

    /**
     * Sets the edit template.
     * @param string $view name of the template file
     * @return string $view name of the template file
     */
    public function setEditView($view)
    {
        $this->editView = $view;

        return $this->editView;
    }

    /**
     * Gets the edit template.
     * @return string name of the template file
     */
    public function getEditView()
    {
        // if the view exists return it
        if(view()->exists($this->editView)) {
          return $this->editView;
        }

        // otherwise return the default view
        return backpack_view('edit', 'crud');
    }

    /**
     * Sets the reorder template.
     * @param string $view name of the template file
     * @return string $view name of the template file
     */
    public function setReorderView($view)
    {
        $this->reorderView = $view;

        return $this->reorderView;
    }

    /**
     * Gets the reorder template.
     * @return string name of the template file
     */
    public function getReorderView()
    {
        // if the view exists return it
        if(view()->exists($this->reorderView)) {
          return $this->reorderView;
        }

        // otherwise return the default view
        return backpack_view('reorder', 'crud');
    }

    /**
     * Sets the revision template.
     * @param string $view name of the template file
     * @return string $view name of the template file
     */
    public function setRevisionsView($view)
    {
        $this->revisionsView = $view;

        return $this->revisionsView;
    }

    /**
     * Sets the revision template.
     * @param string $view name of the template file
     * @return string $view name of the template file
     */
    public function setRevisionsTimelineView($view)
    {
        $this->revisionsTimelineView = $view;

        return $this->revisionsTimelineView;
    }

    /**
     * Gets the revisions template.
     * @return string name of the template file
     */
    public function getRevisionsView()
    {
        // if the view exists return it
        if(view()->exists($this->revisionsView)) {
          return $this->revisionsView;
        }

        // otherwise return the default view
        return backpack_view('revisions', 'crud');
    }

    /**
     * Gets the revisions template.
     * @return string name of the template file
     */
    public function getRevisionsTimelineView()
    {
        // if the view exists return it
        if(view()->exists($this->revisionsTimelineView)) {
          return $this->revisionsTimelineView;
        }

        // otherwise return the default view
        return backpack_view('inc.revision_timeline', 'crud');
    }

    // -------
    // ALIASES
    // -------

    public function getPreviewView()
    {
        return $this->getShowView();
    }

    public function setPreviewView($view)
    {
        return $this->setShowView($view);
    }

    public function getUpdateView()
    {
        return $this->getEditView();
    }

    public function setUpdateView($view)
    {
        return $this->setEditView($view);
    }
}
