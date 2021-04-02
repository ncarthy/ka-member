import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { ReportsLayoutComponent } from './layout.component';
import { ReportsComponent } from './reports.component';
import { MailingListComponent } from './mailing-list';
import { EmailListComponent } from './email-list';
import { MemberListComponent } from './member-list';
import { InvalidEmailsComponent } from './invalid-emails';
import { InvalidPostcodesComponent } from './invalid-postcodes';

const routes: Routes = [
    {
        path: '', component: ReportsLayoutComponent,
        children: [
            { path: '', component: ReportsComponent },
            { path: 'mailing-list', component: MailingListComponent },
            { path: 'email-list', component: EmailListComponent },
            { path: 'lapsed', component: MemberListComponent },
            { path: 'cem', component: MemberListComponent },
            { path: 'honlife', component: MemberListComponent },
            { path: 'discount', component: MemberListComponent },
            { path: 'duplicatepayers', component: MemberListComponent },
            { path: 'noukaddress', component: MemberListComponent },
            { path: 'invalidemails', component: InvalidEmailsComponent },
            { path: 'invalidpostcodes', component: InvalidPostcodesComponent },
            { path: 'deletedmembers', component: MemberListComponent },
        ]
    }
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class ReportsRoutingModule { }