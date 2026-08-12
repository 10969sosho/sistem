import LeadDetail from './lead-detail';

export function generateStaticParams() {
  return [{ id: 'placeholder' }];
}

export default function Page() {
  return <LeadDetail />;
}
