<?php
namespace App\Controller;
use Cake\Network\Exception\NotFoundException;

class ArticlesController extends AppController
{
    public $components = ['Flash'];

    public function index()
    {
        $this->set('articles', $this->Articles->find('all'));
    }

    public function view($id)
    {
        $article = $this->Articles->get($id);
        $this->set(compact('article'));
    }


    public function add()
    {
        $article = $this->Articles->newEntity();
        if ($this->request->is('post')) {
            $article = $this->Articles->patchEntity($article, $this->request->data);
            $article->user_id = $this->Auth->user('id');
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Your article has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Unable to add your article.'));
        }
        $this->set('article', $article);

        // Just added the categories list to be able to choose
        // one category for an article
        $categories = $this->Articles->Categories->find('treeList');
        $this->set(compact('categories'));
    }

    public function edit($id = null)
	{
	    $article = $this->Articles->get($id);
	    if ($this->request->is(['post', 'put'])) {
	        $this->Articles->patchEntity($article, $this->request->data);
	        if ($this->Articles->save($article)) {
	            $this->Flash->success(__('Tu artículo ha sido actualizado.'));
	            return $this->redirect(['action' => 'index']);
	        }
	        $this->Flash->error(__('Tu artículo no se ha podido actualizar.'));
	    }

	    $this->set('article', $article);
	}
	public function delete($id)
	{
	    $this->request->allowMethod(['post', 'delete']);

	    $article = $this->Articles->get($id);
	    if ($this->Articles->delete($article)) {
	        $this->Flash->success(__('El artículo con id: {0} ha sido eliminado.', h($id)));
	        return $this->redirect(['action' => 'index']);
	    }
	}

    public function isAuthorized($user)
    {
        // All registered users can add articles
        if ($this->request->action === 'add') {
            return true;
        }

        // The owner of an article can edit and delete it
        if (in_array($this->request->action, ['edit', 'delete'])) {
            $articleId = (int)$this->request->params['pass'][0];
            if ($this->Articles->isOwnedBy($articleId, $user['id'])) {
                return true;
            }
        }

        return parent::isAuthorized($user);
    }
}