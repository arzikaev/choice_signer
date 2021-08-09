<?php
require_once('/var/www/html/userfield/crestapp/src/crest.php');

if (!empty($_REQUEST['PLACEMENT_OPTIONS'])) {
    //CRest::writeToLog($_REQUEST, 'request true');

    $placementOption = json_decode($_REQUEST['PLACEMENT_OPTIONS'], true);
    $domain = $_REQUEST['DOMAIN'];
    $result = CRest::call(
        'crm.deal.contact.items.get',
        ['id' => $placementOption['ENTITY_VALUE_ID']],
        $_REQUEST['DOMAIN']
    );
    $contacts = $result['result'];

    foreach ($contacts as $contact) {
        $contactInfo = [
            'ID' => '',
            'NAME' => '',
            'LAST_NAME' => '',
            'IS_PRIMARY' => ''
        ];
        $contactData = CRest::call(
            'crm.contact.get',
            ['id' => $contact['CONTACT_ID']],
            $_REQUEST['DOMAIN']
        );
        //CRest::writeToLog($contactData, 'contactData');
        //CRest::writeToLog($domain, 'domain');

        $contactInfo['ID'] = $contact['CONTACT_ID'];
        $contactInfo['IS_PRIMARY'] = $contact['IS_PRIMARY'];
        if (!empty($contactData['result']['NAME'])) $contactInfo['NAME'] = $contactData['result']['NAME'];
        if (!empty($contactData['result']['LAST_NAME'])) $contactInfo['LAST_NAME'] = $contactData['result']['LAST_NAME'];
        $contactsData[] = $contactInfo;
    }
} else {
   // CRest::writeToLog($_REQUEST, 'request');

    $items = [];
    foreach ($_REQUEST['items'] as $e) {
        $items[] = json_decode($e);
    }
    $result = CRest::call(
        'crm.deal.contact.items.set',
        ['id' => $_REQUEST['id'], 'items' => $items],
        $_REQUEST['domen']
    );
    //CRest::writeToLog($_REQUEST, 'request');
    //CRest::writeToLog($items, 'items');
    //CRest::writeToLog($result, 'result');

}

?>
<!DOCTYPE html>
<html>
<head>
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">
</head>
<body>
<div id="app">
    <v-app>
        <v-main>
            <v-container
                    class="ma-0 pa-0"
                    fluid
                    id="container"
            >
                <v-row>
                    <v-col cols="12">
                        <v-radio-group v-model="setContact" @change="setContacts">
                            <v-radio
                                    v-for="contact in contacts"
                                    :key="contact.ID"
                                    :label="contact.NAME + ' ' + contact.LAST_NAME"
                                    :value="contact.ID"
                            ></v-radio>
                        </v-radio-group>
                    </v-col>
                </v-row>
            </v-container>
        </v-main>
    </v-app>
</div>

<script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script src="//api.bitrix24.com/api/v1/"></script>

<script>
    new Vue({
        el: '#app',
        vuetify: new Vuetify(),
        data() {
            return {
                contactsData: <?php echo json_encode($contactsData); ?>,
                contacts: [],
                setContact: '',
                defaultContactID: '',
                returnData: [],
                dealID: <?php echo $placementOption['ENTITY_VALUE_ID']; ?>,
                domen: '<?php echo $domain; ?>'
        }
        },
        methods: {
            setContacts() {
                console.log(this.setContact)
                console.log(this.contactsData)

                const contactsToDeal = []
                for (let elem of this.contactsData) {
                    console.log(elem)
                    if (elem['ID'] === this.setContact) {
                        elem['IS_PRIMARY'] = 'Y'
                    } else {
                        elem['IS_PRIMARY'] = 'N'
                    }
                    contactsToDeal.push({IS_PRIMARY: elem['IS_PRIMARY'], CONTACT_ID: elem['ID']})
                }
                this.returnData = contactsToDeal
                this.inPHP(contactsToDeal)
            },
            getContacts() {
                BX24.resizeWindow(10, 80)
                if (this.contactsData.length > 0) {
                    for (let item of this.contactsData) {
                        console.log(item)
                        let contact = {}
                        contact.ID = item.ID
                        if (item.NAME !== null) contact.NAME = item.NAME
                        if (item.LAST_NAME !== null) contact.LAST_NAME = item.LAST_NAME
                        if (item['IS_PRIMARY'] === 'Y') this.setContact = contact.ID
                        this.contacts.push(contact)
                    }
                } else {
                    console.log('в сделке нет ниодного контакта!')
                }
            },
            inPHP(data) {
                console.log(data)
                console.log(this.dealID)
                axios({
                    method: 'get',
                    url: 'https://spa-bitrix.ru/userfield/crestapp/field/handler.php',
                    params: {
                        id: this.dealID,
                        items: data,
                        domen: this.domen
                    }
                })
                    .then(function (response) {
                        console.log(response);
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            }
        },
        mounted() {
            this.getContacts()
        }
    })
</script>
</body>
</html>