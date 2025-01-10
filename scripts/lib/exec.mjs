import { execSync } from 'node:child_process';

export const execDockerWp = (command = '') => {
  try {
    return execSync(`docker exec ${process.env.PROJECT_NAME}-wordpress ${command}`);
  } catch (error) {
    console.error(`[ERROR]`, error.toString());
  }
};
export const execWpCli = (command = '') => {
  try {
    return execDockerWp(`env sudo --preserve-env -u www-data wp ${command}`);
  } catch (error) {
    console.error(`[ERROR]`, error.toString());
  }
};
