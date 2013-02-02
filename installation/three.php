<?php // @TODO more settings?!
if(fSession::get('maxStep') < 3)
    fURL::redirect('?step=two');

$tpl = new fTemplating($this->get('tplRoot'), 'three.tpl');
$this->set('tpl', $tpl);

if(fRequest::isPost() && fRequest::get('general_submit')) {
    /*
    * store input values
    */
    $tpl->set('adminpw', fRequest::encode('adminpw'));
    $tpl->set('title', fRequest::encode('title'));

    try {
        $vali = new fValidation();

        $vali->addRequiredFields(array(
                                      'adminpw',
                                      'title'
                                 ))
            ->overrideFieldName('adminpw', 'Admin Password')
            ->validate();


        // writing db
        $db = fORMDatabase::retrieve();

        // writing general settings
        $sql = $db->translatedPrepare('INSERT INTO "prefix_settings" (`key`, `value`) VALUES(%s, %s)');
        $db->execute($sql, 'adminpw', fCryptography::hashPassword(fRequest::get('adminpw', 'string')));
        $db->execute($sql, 'title', fRequest::encode('title', 'string'));

    } catch(fValidationException $e) {
        fMessaging::create('validation', 'install/three', $e->getMessage());
    } catch(fConnectivityException $e) {
        fMessaging::create('connectivity', 'install/three', $e->getMessage());
    } catch(fAuthorizationException $e) {
        fMessaging::create('auth', 'install/three', $e->getMessage());
    } catch(fNotFoundException $e) {
        fMessaging::create('notfound', 'install/three', $e->getMessage());
    } catch(fSQLException $e) {
        fMessaging::create('sql', 'install/three', $e->getMessage());
    }

    if(!fMessaging::check('*', 'install/three')) {
        fSession::set('maxStep', 4);
        fURL::redirect('?step=four');
    }
}
