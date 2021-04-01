import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

import { ReportsRoutingModule } from './reports-routing.module';
import { SharedModule } from '@app/shared/shared.module';

import { ReportsLayoutComponent } from './layout.component';
import { ReportsComponent } from './reports.component';
import { MailingListComponent} from './mailing-list';
import { EmailListComponent} from './email-list';
import { MemberListComponent } from './member-list/member-list.component';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        ReportsRoutingModule,
        SharedModule
    ],
    declarations: [
        ReportsComponent,
        ReportsLayoutComponent,
        MailingListComponent,
        EmailListComponent,
        MemberListComponent,
    ]
})
export class ReportsModule { }