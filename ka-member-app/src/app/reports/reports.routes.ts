import { Routes } from '@angular/router';

import { ReportsComponent } from './reports.component';
import { MailingListComponent } from './mailing-list';
import { EmailListComponent } from './email-list';
import { NoEmailListComponent } from './no-email-list';
import { MapListComponent } from './map-list';
import { MemberListReportComponent } from './member-list';
import { InvalidEmailsComponent } from './invalid-emails';
import { InvalidPostcodesComponent } from './invalid-postcodes';
import { TransactionsSummaryComponent } from './transactions';

export const REPORTS_ROUTES: Routes = [
  { path: '', component: ReportsComponent },
  { path: 'mailing-list', component: MailingListComponent },
  { path: 'email-list', component: EmailListComponent },
  { path: 'no-email-list', component: NoEmailListComponent },
  { path: 'map-list', component: MapListComponent },
  { path: 'lapsed/:months', component: MemberListReportComponent },
  { path: 'cem', component: MemberListReportComponent },
  { path: 'honlife', component: MemberListReportComponent },
  { path: 'discount', component: MemberListReportComponent },
  { path: 'duplicatepayers', component: MemberListReportComponent },
  { path: 'noukaddress', component: MemberListReportComponent },
  { path: 'invalidemails', component: InvalidEmailsComponent },
  { path: 'invalidpostcodes', component: InvalidPostcodesComponent },
  { path: 'deletedmembers', component: MemberListReportComponent },
  { path: 'lapsedcem/:months', component: MemberListReportComponent },
  { path: 'formermembers/:months', component: MemberListReportComponent },
  { path: 'oldformermembers/:months', component: MemberListReportComponent },
  { path: 'transactions', component: TransactionsSummaryComponent },
];
