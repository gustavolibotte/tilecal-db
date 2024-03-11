import { useRouteError } from 'react-router-dom';
import PageLayout from '../../components/page_layout/PageLayout';
import './error_page.css'

interface ErrorPageProps {
  useLayout?: boolean
}
export default function ErrorPage(props: ErrorPageProps){
  const error: any = useRouteError();
  console.error(error);
  const baseNode = (
    <div className="jumbotron text-center align-items-center">
      <h1>An error has occured!</h1>
      <p>{error.statusText}</p>
      <p>If everything should have worked, please contact an admin.</p>
    </div>
  );
  if (props.useLayout){
    return (
      <PageLayout>
        {baseNode}
      </PageLayout>
    )
  }
  return baseNode;
}
