<template>
<v-app id="inspire">
    <app-menu :drawer="drawer"></app-menu>
    <app-header></app-header>
    <v-content>
        <v-container fluid fill-height>
            <v-layout justify-center align-center>

                <!-- Main -->
                <div class="panel panel-default">
                    <div class="panel-heading">Dashboard</div>
                    <div class="panel-body">You are logged in!</div>
                </div>
                <!-- Main -->

            </v-layout>
        </v-container>
    </v-content>
    <app-footer></app-footer>
</v-app>
</template>

<script>
const eventHub = new Vue();

export default {
    props: {
        eventHub: eventHub,
    },
    data: () => ({
        drawer: true,
    })
}
</script>
