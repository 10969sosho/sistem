import TaskDetail from './task-detail';

export function generateStaticParams() {
  return [{ id: 'placeholder' }];
}

export default function Page() {
  return <TaskDetail />;
}
