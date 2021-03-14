import { NgModule, APP_INITIALIZER } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { ReactiveFormsModule } from '@angular/forms';
import { HttpClientModule, HTTP_INTERCEPTORS } from '@angular/common/http';

import { AppComponent } from './app.component';
import { AppRoutingModule } from './app-routing.module';

import { JwtInterceptor, ErrorInterceptor, appInitializer } from './_helpers';
import { AuthenticationService } from './_services';
import { AlertComponent } from './_components';

import { HomeComponent } from './home';
import { LoginComponent } from './login';
import { AdminComponent } from './admin';
import { ToastContainerComponent } from './toast-container/toast-container.component';

import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { MembersModule } from './members/members.module';
import { MemberSearchModule } from './member-search/member-search.module';
import { AddressSearchModule } from './address-search/address-search.module';

@NgModule({
    imports: [
        BrowserModule,
        ReactiveFormsModule,
        HttpClientModule,
        AppRoutingModule,
        NgbModule,
        MembersModule,
        MemberSearchModule,
        AddressSearchModule
    ],
    declarations: [
        AppComponent,
        HomeComponent,
        LoginComponent,
        AdminComponent,
        ToastContainerComponent,
        AlertComponent        
    ],
    providers: [
        { provide: APP_INITIALIZER, useFactory: appInitializer, multi: true, deps: [AuthenticationService] },
        { provide: HTTP_INTERCEPTORS, useClass: JwtInterceptor, multi: true },
        { provide: HTTP_INTERCEPTORS, useClass: ErrorInterceptor, multi: true },
    ],
    bootstrap: [AppComponent]
})
export class AppModule { }