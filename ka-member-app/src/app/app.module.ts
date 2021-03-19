import { NgModule, APP_INITIALIZER } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { HttpClientModule, HTTP_INTERCEPTORS } from '@angular/common/http';

import { AppComponent } from './app.component';
import { AppRoutingModule } from './app-routing.module';

import { JwtInterceptor, ErrorInterceptor, appInitializer } from './_helpers';
import { AuthenticationService } from './_services';
import { AlertComponent } from './_components';

import { HomeComponent } from './home';
import { LoginComponent } from './login';
import { AdminComponent } from './admin';

import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { MembersModule } from './members/members.module';
import { MemberSearchModule } from './member-search/member-search.module';
import { SharedModule } from '@app/shared/shared.module';
import { ToastContainerComponent } from './shared/toast-container/toast-container.component';

@NgModule({
    imports: [
        BrowserModule,
        FormsModule,
        ReactiveFormsModule,
        HttpClientModule,
        AppRoutingModule,
        NgbModule,
        MembersModule,
        MemberSearchModule,
        SharedModule
    ],
    declarations: [
        AppComponent,
        HomeComponent,
        LoginComponent,
        AdminComponent,
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